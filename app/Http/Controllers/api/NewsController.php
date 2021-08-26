<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\News;

use DB;
use Storage;
use Image;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $order = 'desc', $tag = null, $offset = null, $limit = null)
    {
        if ($request->order != null && ($request->order != "asc" && $request->order != "desc")) {
            return response()->json(['error' => "Invalid Order"], 400);
        }

        $news = News::with('tags');

        if($request->tag) {
            $news = $news->whereHas('tags', function($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }
        $news->orderBy('created_at', $request->order ?? 'DESC');

        if($request->offset) {
            // $news = News::skip($offset)->take($limit)->with('tags')->get();
            $news->skip($request->offset);
        }

        if($request->limit) {
            $news->limit($request->limit);
        }


        $news = $news->get();

        $news->map(function($item) {
            $item->image = url('/storage/image/'.$item->image);
            $item->thumbnail_image = url('/storage/image/compressed/'.$item->thumbnail_image);
            $item->raw_content = trim(strip_tags($item->content));
        });

        $total = News::count();

        return response()->json(['total' => $total, 'news' => $news], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $news = News::find($id);
        if(!$news) {
            $news = News::where('slug', $id)->first();
            
        }
        $news->image = url('/storage/image/'.$news->image);
        $news->thumbnail_image = url('/storage/image/compressed/'.$news->thumbnail_image);
        $news->raw_content = trim(strip_tags($news->content));
        return $news->load('tags');
    }

    public function getBySlug($slug)
    {
        $news = News::where('slug', $slug)->first();
        $news->image = url('/storage/image/'.$news->image);
        $news->thumbnail_image = url('/storage/image/compressed/'.$news->thumbnail_image);
        $news->raw_content = trim(strip_tags($news->content));
        return $news->load('tags');
    }

    public function sortByDate($order = "asc", $offset = null, $limit = null)
    {
        if ($order != "asc" && $order != "desc") {
            return response()->json(['error' => "Invalid Order"], 400);
        }

        $news = News::with('tags');

        if($offset && $limit) {
            $news->skip($offset)->take($limit);
        }

        $news = $news->orderBy('created_at', $order)->get();

        $news->map(function($item) {
            $item->image = url('/storage/image/'.$item->image);
            $item->thumbnail_image = url('/storage/image/compressed/'.$item->thumbnail_image);
            $item->raw_content = trim(strip_tags($item->content));
        });

        return $news;
    }

    public function sortByDateWithTag($order = 'asc', $tag, $offset = null, $limit = null)
    {
        if ($order != "asc" && $order != "desc") {
            return response()->json(['error' => "Invalid Order"], 400);
        }

        $news = News::whereHas('tags', function($q) use ($tag) {
            $q->where('name', $tag);
        })->with('tags')->orderBy('created_at', 'desc');

        if($offset && $limit) {
            $news->skip($offset)->take($limit);
        }
        
        $news = $news->get();

        $news->map(function($item) {
            $item->image = url('/storage/image/'.$item->image);
            $item->thumbnail_image = url('/storage/image/compressed/'.$item->thumbnail_image);
            $item->raw_content = trim(strip_tags($item->content));
        });

        return $news;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validate = validator($data , [
            'title'             => 'required',
            'content'           => 'required',
            'tags'              => 'required|array',
            'tags.*.name'       => 'required',
            'image'             => 'required|array',
            'image.filename'  => 'required',
            'image.base64'    => 'required'
        ])->validate();

        // $news = null;

        $news = DB::transaction(function () use ($data, $request) {
            $path = 'image/' . $data["image"]["filename"] . "-" . $randomNumber . '.png';
            $compressedPath = 'image/compressed/' . $data["image"]["filename"] . "-" . $randomNumber . '.png';
            $image = Image::make($data["image"]["base64"]);
            $image->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
    
            Storage::disk('public_upload')->put($path, $image->encode());
            Storage::disk('public_upload')->put($compressedPath, (string) $image->encode($image->mime, 40));
    
            
            $news = News::create([
                'title'             => $data["title"],
                'content'           => $data["content"],
                'image'             => basename($path),
                'thumbnail_image'   => basename($compressedPath),
                'published'         => 0,
                'created_by'        => $request->user()->name,
            ]);
    
            foreach ($data["tags"] as $key => $value) {
                
                if($value['id'] == null) {
                    $newTag = Tag::firstOrCreate(['name' => $value["name"]]);
                    $tagId = $newTag->id;
                } else {
                    $tagId = $value['id'];
                }
                $news->tags()->attach($tagId);
                
            }
    
            $news->image = url('/storage/image/'.$news->image);
            $news->thumbnail_image = url('/storage/image/compressed/'.$news->thumbnail_image);
            $news->raw_content = trim(strip_tags($news->content));
            return $news;
        });


        return $news->load('tags');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, News $news)
    {
        $data = $request->all();
        $validate = validator($data , [
            'tags'              => 'array',
            'image'             => 'array',
        ])->validate();

        DB::transaction(function () use ($data, $news, $request) {
            if(isset($data["image"])) {

                Storage::disk('public_upload')->delete('/image/' . $news->image);
                Storage::disk('public_upload')->delete('/image/compressed/' . $news->thumbnail_image);

                $randomNumber = rand();
                $path = 'image/' . $data["image"]["filename"] . "-" . $randomNumber . '.png';
                $compressedPath = 'image/compressed/' . $data["image"]["filename"] . "-" . $randomNumber . '.png';
                $image = Image::make($data["image"]["base64"]);
                $image->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
        
                Storage::disk('public_upload')->put($path, $image->encode());
                Storage::disk('public_upload')->put($compressedPath, (string) $image->encode($image->mime, 40));

                $data["image"] = basename($path);
                $data["thumbnail_image"] = basename($compressedPath);
            }
            
            $news->update($data);

            if(isset($data["tags"])) {
                $tagId = [];
                foreach ($data["tags"] as $key => $value) {
                    if($value['id'] == null) {
                        $newTag = Tag::firstOrCreate(['name' => $value["name"]]);
                        array_push($tagId, $newTag->id);
                    } else {
                        array_push($tagId, $value['id']);
                    }
                    
                }
                $news->tags()->sync($tagId);
            }
    
    
            $news->image = url('/storage/image/'.$news->image);
            $news->thumbnail_image = url('/storage/image/compressed/'.$news->thumbnail_image);
            $news->raw_content = trim(strip_tags($news->content));
        });

        return $news->load('tags');
    }

    public function destroy(News $news)
    {
        DB::transaction(function () use ($news){
            Storage::disk('public_upload')->delete('/image/' . $news->image);
            Storage::disk('public_upload')->delete('/image/compressed/' . $news->thumbnail_image);

            $news->tags()->detach();

            $news->delete();
        });


        return response()->json(['message' => 'Delete Successful'], 200);
    }

    
    public function contentUploadImage(Request $request) {
        $randomNumber = rand();
        $file = $request->file('upload');
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = $file->getClientOriginalExtension();     
        // $imageName = $filename . "-" . $randomNumber . '-' . auth()->id() . '.' . $ext;
        $imageName = $filename . "-" . $randomNumber . '.' . $ext;

        $path = $file->storeAs('news-content-image', $imageName, 'public_upload');
        // Storage::disk('public_upload')->putFileAs('news-content-image/', $file);
        return response()->json(['url' => url('/storage/news-content-image/' . $imageName)], 200);
    }

}

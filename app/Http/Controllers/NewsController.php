<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Image;
use Storage;
use DB;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allFiles = Storage::disk('public_upload')->allFiles('news-content-image');
        if($allFiles) {
            Storage::disk('public_upload')->delete($allFiles);
        }

        $news = News::orderBy('created_at', 'desc')->with('tags')->paginate(10);

        $this->newsMapping($news);
        
        $data['news'] = $news;

        // return $news[0]->tags;

        return view('news.index', $data);

    }

    private function newsMapping($news) {
        $news->map(function($item) {
            $item->image = url('/upload/image/'.$item->image);
            $item->thumbnail_image = url('/upload/image/compressed/'.$item->thumbnail_image);
            $item->raw_content = Str::limit(trim(strip_tags($item->content)), 175, '...');
            // $item->raw_content = trim(strip_tags($item->content));
            $item->title = Str::limit($item->title,50,'...');
        });
    }

    public function search($keyword)
    {
        $news = News::where('title','like',"%$keyword%")->orderBy('created_at', 'desc')->paginate(4);

        $this->newsMapping($news);

        $data['news'] = $news;
        $data['keyword'] = $keyword;
        return view('news.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['tags'] = Tag::orderBy('name', 'asc')->get();

        return view('news.create', $data);
    }

    private function getImgSrcAtt($html) {
        $str = $html;
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($str);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);
        // $src = $xpath->evaluate("string(//img/@src)"); # "/images/image.jpg"
        $src = $xpath->query('//img/@src');
        $arr = [];
        foreach($src as $a) {
            array_push($arr, basename($a->value));
        }

        return $arr;
    }

    private function changeImgSrc($html, $newSrcs) {
        try {
            $doc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();
            $imgs = $doc->getElementsByTagName('img');
            if($imgs->length > 0) {
                foreach ($imgs as $key => $value) {
                    $value->setAttribute('src', $newSrcs[$key]);
                }
                $doc->saveHTML();
            }
            // return mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
            $mock = new \DOMDocument();
            $body = $doc->getElementsByTagName('body')->item(0);
            foreach ($body->childNodes as $child){
                $mock->appendChild($mock->importNode($child, true));
            }

            return html_entity_decode(mb_convert_encoding($mock->saveHTML(), 'HTML-ENTITIES', 'UTF-8'));

        } catch (\Throwable $th) {
            throw $th;
        }        
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
            'image'             =>'required|image',
            'title'             => 'required',
            'content'           => 'required',
            'tags'              => 'required|array',
            // 'tags.*.name'       => 'required',
            // 'image'             => 'required|array',
            // 'image.filename'  => 'required',
            // 'image.base64'    => 'required'
        ])->validate();
        // $news = null;


        $news = DB::transaction(function () use ($data, $request) {
            
            //Upload Thumbnail Image
            $randomNumber = rand();

            $file = $request->file('image');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $file->getClientOriginalExtension();
            
            $image = Image::make($file);
            
            $path = 'image/' . $filename . "-" . $randomNumber . '.' . $ext;
            $compressedPath = 'image/compressed/' . $filename . "-" . $randomNumber . '.' . $ext;
            // $image = Image::make($data["image"]["base64"]);
            
            Storage::disk('public_upload')->put($path, $image->encode());
            $image->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            Storage::disk('public_upload')->put($compressedPath, (string) $image->encode($image->mime, 60));

            // Move image to new directory
            $contentImgs = $this->getImgSrcAtt($data['content']);

            foreach ($contentImgs as $key => $contentImg) {
                Storage::disk('public_upload')->move('news-content-image/' . $contentImg, 'image/news-content-image/' .  $contentImg);

                // change to full url for img src purpose
                $contentImgs[$key] = url('upload/image/news-content-image/' . $contentImg);
            }

            // Change img src in content to match the new directory
            $data['content'] = $this->changeImgSrc($data['content'], $contentImgs);

            // Save to DB
            $slug = Str::slug($data['title'], '-');
            $newNews = News::create([
                'title'             => $data["title"],
                'slug'              => $slug,
                'content'           => $data["content"],
                'image'             => basename($path),
                'thumbnail_image'   => basename($compressedPath),
                'published'         => 0,
                'created_by'        => $request->user()->name,
            ]);

            // Attach Many to Many (News Tags)
            $newNews->tags()->attach($data["tags"]);
    
            $newNews->image = url('/upload/image/'.$newNews->image);
            $newNews->thumbnail_image = url('/upload/image/compressed/'.$newNews->thumbnail_image);

            return $newNews;
        });


        return redirect()->route('news.index')->with('message','News has been Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function show(News $news)
    {
        $news->image = url('/upload/image/'.$news->image);
        $news->thumbnail_image = url('/upload/image/compressed/'.$news->thumbnail_image);

        return $news->load('tags');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function edit(News $news)
    {
        $data['tags'] = Tag::orderBy('name', 'asc')->get();
        $news->image = url('/upload/image/'.$news->image);
        $news->thumbnail_image = url('/upload/image/compressed/'.$news->thumbnail_image);
        $data['news'] = $news->load('tags');
        // return $data['news'];

        return view('news.edit', $data);
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
            // 'image'             =>'required|image',
            'title'             => 'required',
            'content'           => 'required',
            'tags'              => 'required|array',
            // 'tags.*.name'       => 'required',
            // 'image'             => 'required|array',
            // 'image.filename'  => 'required',
            // 'image.base64'    => 'required'
        ])->validate();
        // if(Storage::disk('public_upload')->exists('image/news-content-image/' . 'cleo smug-1392025913.png')) {
        //     // Storage::disk('public_upload')->delete('image/news-content-image/' . $value);
        //     return "true";
        // }

        // return "false";

        DB::transaction(function () use ($data, $news, $request) {
            if(isset($data["image"])) {

                Storage::disk('public_upload')->delete('/image/' . $news->image);
                Storage::disk('public_upload')->delete('/image/compressed/' . $news->thumbnail_image);

                $randomNumber = rand();

                $file = $data['image'];
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = $file->getClientOriginalExtension();
                
                $image = Image::make($file);
                
                $path = 'image/' . $filename . "-" . $randomNumber . '.' . $ext;
                $compressedPath = 'image/compressed/' . $filename . "-" . $randomNumber . '.' . $ext;
                // $image = Image::make($data["image"]["base64"]);
                
                Storage::disk('public_upload')->put($path, $image->encode());
                $image->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                Storage::disk('public_upload')->put($compressedPath, (string) $image->encode($image->mime, 60));

                $data["image"] = basename($path);
                $data["thumbnail_image"] = basename($compressedPath);
            }

            if($request->content) {

                $oldContentImgs = $this->getImgSrcAtt($news->content);
                $contentImgs = $this->getImgSrcAtt($data['content']);
    
                $diff = array_diff($oldContentImgs, $contentImgs);
    
                foreach ($diff as $key => $value) {
                    // Delete old images
                    if(Storage::disk('public_upload')->exists('image/news-content-image/' . urldecode($value))) {
                        Storage::disk('public_upload')->delete('image/news-content-image/' . urldecode($value));
                    }
                }
    
                foreach ($contentImgs as $key => $contentImg) {
                    // Move image to new directory
                    if(Storage::disk('public_upload')->exists('news-content-image/' . $contentImg)) {
                        Storage::disk('public_upload')->move('news-content-image/' . $contentImg, 'image/news-content-image/' .  $contentImg);
    
                    }
                    // change to full url for img src purpose
                    $contentImgs[$key] = url('upload/image/news-content-image/' . $contentImg);
    
                }
    
                // Change img src in content to match the new directory
                $data['content'] = $this->changeImgSrc($data['content'], $contentImgs);
            }
            $data['slug'] = Str::slug($data['title'], '-');
            $news->update($data);
            if($request->tags) {
                $news->tags()->sync($data["tags"]);
            }
    
            $news->image = url('/upload/image/'.$news->image);
            $news->thumbnail_image = url('/upload/image/compressed/'.$news->thumbnail_image);

        });

        return redirect()->route('news.index')->with('message','News has been Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function destroy(News $news)
    {
        DB::transaction(function () use ($news){
            Storage::disk('public_upload')->delete('/image/' . $news->image);
            Storage::disk('public_upload')->delete('/image/compressed/' . $news->thumbnail_image);

            $contentImgs = $this->getImgSrcAtt($news->content);

            foreach ($contentImgs as $key => $value) {
                Storage::disk('public_upload')->delete('/image/news-content-image/' . urldecode($value));
            }

            $news->tags()->detach();

            $news->delete();
        });


        return redirect()->route('news.index')->with('message','News has been Deleted');
    }
}

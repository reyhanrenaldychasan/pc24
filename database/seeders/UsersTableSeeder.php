<?php

namespace Database\Seeders;
use Illuminate\Support\Carbon;

use Illuminate\Database\Seeder;
use App\Models\User;
use DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user = User::where('username', 'Admin')->first();
        if($user) {
            
        } else {
            DB::table('users')->insert([
                [
                    'name'          => 'Admin',
                    'username'      => 'Admin',
                    'email'         => 'Admin@admin.com',
                    'password'      => bcrypt('secret'),
                    'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at'    => Carbon::now()->format('Y-m-d H:i:s'),
                ],
            ]);
        }
    }
}

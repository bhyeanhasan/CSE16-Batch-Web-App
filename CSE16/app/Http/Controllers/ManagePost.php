<?php
namespace App\Http\Controllers;
use App\Models\blog_post;
use App\Models\Notice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class ManagePost extends Controller
{

    function create_post(Request $request)
    {
        $pictures_data              = array();

        #kno image deoa hoice kina check korbe
        if($request->hasFile('image'))
        {
            $post_image_name_generated  = hexdec(uniqid());
            $post_image_path            = 'image/post/';
            $num                        = 0;

            #ekadhik image er jonno protita save korebo
            foreach ($request->file('image') as $picture)
            {
                $picture_name       = $post_image_name_generated.$num.".".$picture->extension();
                $picture_database   = $post_image_path.$picture_name;
                $pictures_data []   = $picture_database;        #image gula array te rakhbo
                $num                = $num+1;
                $picture->move($post_image_path,$picture_name);
            }
        }

        $blog_post_data = blog_post::insert([
            'posted_by'         => Auth::user()->name,
            'poster_email'      => Auth::user()->email,
            'post_heading'      => $request->heading,
            'post_body'         => $request->body,
            'post_pictures'     => implode("|",$pictures_data),
            'created_at'        => Carbon::now(),
        ]);

        return back()->with('SuccessFull');

    }

    function view_all_post($id)
    {
        $view_post = null;

        if($id != 0)
        {
            $view_post = DB::table('blog_posts')
                ->join('students','blog_posts.poster_email','=','students.email')
                ->select('blog_posts.*','students.profile_pic','students.roll')
                ->where('blog_posts.id',$id)
                ->get()->first();
        }



        $posts = DB::table('blog_posts')
            ->join('students','blog_posts.poster_email','=','students.email')
            ->select('blog_posts.*','students.profile_pic','students.roll')
            ->get();

            return view('blog_post.posts',compact('posts','view_post'));



    }

    function delete_post($id)
    {
        $post =blog_post::find($id);

        if($post->post_picture != null)
        {
            foreach(explode('|',$post->post_pictures) as $picture)
            {
                if(file_exists($picture))
                {
                    unlink($picture);
                }
            }

        }

        $post->delete();
        return redirect()->back();
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Instagram;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AppController extends Controller
{
    private $client;
    private $access_token;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.instagram.com/v1/',
        ]);
    }

    public function redirectToInstagramProvider()
    {
        $res = Socialite::with('instagram')->scopes(["basic likes comments",
            "public_content"])
            ->redirectUrl('http://localhost:8000/instagram/callback');

        return $res->redirect();
        // $res = Socialite::driver('instagram')->redirect();
        // dd($res);
    }

    public function handleProviderInstagramCallback()
    {        
        $user = Socialite::driver('instagram')->stateless()
        ->redirectUrl('http://localhost:8000/instagram/callback')
        ->user();
        //dd($user);
        //dd(request()->all());
        
        $this->access_token = $user->token;
        $instaId = $user->id;
        //dd($instaId);
        $dataUserInsta = json_decode($this->getUser())->data;
        $instaName = $dataUserInsta->username;
        $following = $dataUserInsta->counts->follows;
        $followers = $dataUserInsta->counts->followed_by;
        $posts = $dataUserInsta->counts->media;
        $avatar = $dataUserInsta->profile_picture;        

        $dataUserMediaInsta = json_decode($this->getPosts())->data;

        $sumLikes = 0;
        $sumComments = 0;
        foreach($dataUserMediaInsta as $data)
        {            
            $likes = $data->likes->count;            
            $sumLikes += $likes;

            $comments = $data->comments->count;
            $sumComments += $comments;
        }       
               
        $lastImage = $dataUserMediaInsta[0]->images->standard_resolution->url;   
        $egagement = ($sumComments + $sumLikes)/$followers * 100;
        $egagement = round($egagement, 2);       
        //dd($instaName);
        $User = User::where('insta_id', $instaId)->first();        
        //dd($User);        
        if ($User == null){
            $User = User::create([
                'insta_id' => $instaId,
                'access_token' => $this->access_token,
                'insta_name' => $instaName
            ]);            
        }  
        
        //$insta = Instagram::where('user_id', $userId)->orderBy('created_at')->first();
        $insta = $User->instagram()->first();        
        //dd('asdasdasd',$insta);
        if ($insta == null){
            echo 'qweqweqwe';
            //dd('asdasdasd',$insta);
            $insta = $User->instagram()->create([                                
                'followers' => $followers,                
                'following' => $following,                
                'likes' => $sumLikes,                
                'comments' => $sumComments,
                'post' => $posts,                
                'engagement' => $egagement,                
                'avatar' => $avatar,
                'l_image' => $lastImage
            ]);
            //dd('insta:',$insta);                    
        } else {
            
            $insta = $insta->orderBy('created_at', 'desc')->first();
            //dd('after', $insta);
            $avgFollowers = $followers - $insta->followers;
            $avgFollowing = $following - $insta->following;
            $avgLikes = $sumLikes - $insta->likes;
            $avgComments = $sumComments - $insta->comments;
            $avgPosts = $posts - $insta->avg_post;
            $avgEngagement = $egagement - $insta->avg_engagement;

            //dd('in elseeee', $User->instagram);
            $instagram = $User->instagram()->create([                             
                'followers' => $followers,
                'avg_followers' => $avgFollowers,
                'following' => $following,
                'avg_following' => $avgFollowing,
                'likes' => $sumLikes,
                'avg_likes' => $avgLikes,
                'comments' => $sumComments,
                'avg_comments' => $avgComments,
                'post' => $posts,
                'avg_post' => $avgPosts,
                'engagement' => $egagement,
                'avg_engagement' => $avgEngagement,
                'avatar' => $avatar,
                'l_image' => $lastImage
            ]);            
            dd('in else', $instagram);
        }
        //dd($instagram);
        //return $instagram->id; 
    }

    public function getUser(){
        if($this->access_token){
            $response = $this->client->request('GET', 'users/self/', [
                'query' => [
                    'access_token' =>  $this->access_token
                ]
            ]);
            return $response->getBody()->getContents();
        }
        return [];
    }
 
    public function getPosts(){
        if($this->access_token){
            $response = $this->client->request('GET', 'users/self/media/recent/', [
                'query' => [
                    'access_token' =>  $this->access_token
                ]
            ]);
            return $response->getBody()->getContents();
        }
        return [];
    }
    
}
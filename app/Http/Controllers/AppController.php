<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Instagram;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        $res = Socialite::with('instagram')->stateless()
                ->scopes(["basic","public_content"]);
        return $res->redirect();        
    }

    public function handleProviderInstagramCallback()
    {    
        //Log::info('handleProviderInstagramCallback123:'.json_encode(request()->all()));        
        $user = Socialite::driver('instagram')
        ->stateless()->scopes(["basic likes comments",
        "public_content"])         
        ->user();
            
        $this->access_token = $user->token;
        $instaId = $user->id;
        
        $dataUserInsta = json_decode($this->getUserInstagram())->data;
        $instaName = $dataUserInsta->username;
        $following = $dataUserInsta->counts->follows;
        $followers = $dataUserInsta->counts->followed_by;
        $posts = $dataUserInsta->counts->media;
        $avatar = $dataUserInsta->profile_picture;        

        $dataUserMediaInsta = json_decode($this->getPostsInstagram())->data;

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
        
        $User = User::where('insta_id', $instaId)->first();        
          
        if ($User == null){
            $User = User::create([
                'insta_id' => $instaId,
                'access_token' => $this->access_token,
                'insta_name' => $instaName
            ]);            
        }        
        
        $insta = $User->instagram()->first();        
        
        if ($insta == null){
            
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
                               
        } else {                     
            $insta = $insta->orderBy('created_at', 'desc')->first();   
            // get created_at insta         
            $created_at = $insta->created_at->format('Y-m-d');
            //dd($created_at);            
            // get data now
            $mytime = Carbon::now()->format('Y-m-d');
            
            if($created_at != $mytime){
                $avgFollowers = $followers - $insta->followers;
                $avgFollowing = $following - $insta->following;
                $avgLikes = $sumLikes - $insta->likes;
                $avgComments = $sumComments - $insta->comments;
                $avgPosts = $posts - $insta->post;
                $avgEngagement = $egagement - $insta->engagement;
                
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
            }           
            
        }
        $dataInsta = $User->instagram()->orderBy('created_at', 'desc')->take(30)->get();

        return response()->json($dataInsta);                 
    }

    public function getUserInstagram()
    {
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
 
    public function getPostsInstagram()
    {
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

    public function getUserFromDB(Request $request)
    {        
        $name = $request->input('name');
        
        $user = User::where('insta_name', $name)->first();
                
        $insta_infa = $user->instagram()->orderBy('created_at', 'desc')->first();        
        
        return response()->json($insta_infa);
    }
        
}
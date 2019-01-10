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
   
    public function getUserFromInsta(Request $request)
    {          
        $this->access_token = $request->insta_token;
        $instaId = $request->insta_id;     
        $instaName = $request->insta_name;   
        
        $dataUserInsta = json_decode($this->getUserDataInstagram($this->client, $this->access_token))->data;
        $instaName = $dataUserInsta->username;
        $following = $dataUserInsta->counts->follows;
        $followers = $dataUserInsta->counts->followed_by;
        $posts = $dataUserInsta->counts->media;
        $avatar = $dataUserInsta->profile_picture;        

        $dataUserMediaInsta = json_decode($this->getPostsInstagram($this->client, $this->access_token))->data;

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
        
        $User->access_token = $this->access_token;
        $User->save();
        
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
            $insta = $User->instagram()->orderBy('created_at', 'desc')->first();   
            // get created_at insta         
            $created_at = $insta->created_at->format('Y-m-d');                        
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
             
        return response()->json(['dataInsta' => $dataInsta, 'instagramName' => $instaName]);  
                            
    }

    public function getUserDataInstagram($client, $token)
    {         
        if($token){
            $response = $client->request('GET', 'users/self/', [
                'query' => [
                    'access_token' =>  $token
                ]
            ]);
            return $response->getBody()->getContents();
        }
        return [];        
    }
 
    public function getPostsInstagram($client, $token)
    {        
        if($token){
            $response = $client->request('GET', 'users/self/media/recent/', [
                'query' => [
                    'access_token' =>  $token
                ]
            ]);
            return $response->getBody()->getContents();
        }
        return [];       
        
    }

    public function getLastDataUserFromDB(Request $request, $insta_name = null)
    { 
        $instaName = $insta_name;

        if(!$instaName) {   
            $instaName = $request->input('insta_name');
        }        
                
        $user = User::where('insta_name', $instaName)->first();
                
        $insta_infa = $user->instagram()->orderBy('created_at', 'desc')->first();        
        
        return response()->json(['dataInsta' => $insta_infa, 'instagramName' => $instaName]);
    }

    public function getUsersFromDB()
    {
        $data = array();
        $users = User::all();        
        foreach($users as $key => $user){
            $instaName = $user->insta_name;
            $dataInsta = $user->instagram()->orderBy('created_at', 'desc')->first();
            $followers = $dataInsta->followers;
            $engagement  =  $dataInsta->engagement;      
            $avatar = $dataInsta->avatar; 
            //$data[$key] = ['instaName' => $instaName, 'dataInsta' => $dataInsta];
            $data[$key] = ['instaName' => $instaName, 'followers' => $followers, 'engagement' => $engagement, 'avatar' => $avatar];
        }
        return response()->json(['dataInsta' => $data]);
    }

    public function saveDataUserInsta()
    {
        $users = User::all();
        foreach($users as $user){
            $token = $user->access_token;
            if($token !== null){

                $client = new Client(['base_uri' => 'https://api.instagram.com/v1/',]);
                $dataUserInsta = json_decode($this->getUserDataInstagram($client, $token))->data;
                $instaName = $dataUserInsta->username;
                $following = $dataUserInsta->counts->follows;
                $followers = $dataUserInsta->counts->followed_by;
                $posts = $dataUserInsta->counts->media;
                $avatar = $dataUserInsta->profile_picture;        
        
                $dataUserMediaInsta = json_decode($this->getPostsInstagram($client, $token))->data;
        
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
                // get users instagram data
                $insta = $user->instagram()->orderBy('created_at', 'desc')->first();

                $created_at = $insta->created_at->format('Y-m-d');                  
                // get data now
                $mytime = Carbon::now()->format('Y-m-d');

                if($created_at != $mytime){
                    $avgFollowers = $followers - $insta->followers;
                    $avgFollowing = $following - $insta->following;
                    $avgLikes = $sumLikes - $insta->likes;
                    $avgComments = $sumComments - $insta->comments;
                    $avgPosts = $posts - $insta->post;
                    $avgEngagement = $egagement - $insta->engagement;
                    
                    $instagram = $user->instagram()->create([                             
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
        
        }      
                
    }
        
}

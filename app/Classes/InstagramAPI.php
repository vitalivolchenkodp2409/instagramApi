<?php
 
namespace App\Classes;
 
use GuzzleHttp\Client;
use App\User;
use Carbon\Carbon;
 
class InstagramAPI
{   
    
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
          //dd('in elseeee', $User->instagram);
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
    
}
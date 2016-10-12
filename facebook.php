<?php
class Facebook{

 

// functions
function aggregate_data($my_page,$my_token){

// database connection


// clear the database table
$sql = " DELETE FROM days;";
$this->run_db_query($sql);


// start of json file
$json_file = '{ 
              "name": " ' . $my_page .'",
              "children": [
              ';



// facebook parameters
$Page_id      = $my_page;
$Access_token = $my_token;
$Graph_api    = "https://graph.facebook.com/v2.8/";
$Fields       = "/posts?fields=likes.limit(0).summary(1),created_time,comments.limit(0).summary(1),shares";

// facebook request link
$facebook_link = $Graph_api . $Page_id . $Fields . "&" ."access_token=". $Access_token;

// facebook request query
$facebook_request = file_get_contents($facebook_link);

// decode the response to associative array
$decoded_results = json_decode($facebook_request,true);

$P_date     = 0;    // post dates
$P_posts    = 0;    // Number of posts
$P_likes    = 0;    // Number post likes
$P_comments = 0;    // Number post comments
$P_shares 	= 0;	  // Number post shares 


// get the first row which contains all the data
$facebook_array_date = $decoded_results['data'];

//print_r($facebook_array_date);  

//die();
  
  echo "<br/>";

   foreach ($facebook_array_date as $posts) {

      // Get the current post created time
      $c_date = substr($posts['created_time'], 0,10);

      // get the number of likes 
      $like_count = $posts['likes']['summary']['total_count'];
     

      // get the number of comments 
      $comment_count = $posts['comments']['summary']['total_count'];

      // get the number of shares
	    $share_count = $posts['shares']['count'];
     


      // check the date 
        // if not empty      
       if ($P_date != 0){

           // check equale
          if ($c_date == $P_date){
            
           // increment the number of post 
           $P_posts +=1;
       
           // increment the number of likes with current like count
           $P_likes +=$like_count;

           // increment the number of comments with current comment count
           $P_comments +=$comment_count;

           // increment the number of shares with the current share count
           $P_shares +=$share_count;

           // generate the json file
             $temp_json_file = ',
             {
              "name" :' . $P_posts .',
                "children":[{
                   "name" : "Likes",
                   "size" :' . $like_count . '
                },
                {
                   "name" : "Comments",
                   "size" : ' . $comment_count . '
                },
                {
                   "name" : "Shares",
                   "size" : ' . $share_count . '
                }
                ]
              }';


             $json_file .=$temp_json_file;                      

          }

          else{  // if the dates do not match (meaning it is different day)

             /* echo "date: " . $P_date ."<br/>";
              echo "Number of posts: " . $P_posts ."<br/>";
              echo "Likes: " . $P_likes . "<br/>";
              echo "Comments" . $P_comments . "<br/>";
              echo "Shares" . $P_shares . "<br/>";

              echo "-----------------------";
              echo "<br/>";
                */
            



            // store the previous day likes/ comments to database 
            $sql = "INSERT INTO days (date, posts, likes,comments,shares)
              VALUES ('$P_date', '$P_posts', '$P_likes','$P_comments','$P_shares')";

              $this->run_db_query($sql);

          
          
            // reset the likes and date to the current number of post likes / comments
            $P_likes 	= $like_count;
            $P_date  	= $c_date;
            $P_posts 	= 1;
            $P_shares 	= $share_count;
            $P_comments = $comment_count;
             

              // finalize the children for the day
              $temp_json_file = ' ]
                        },
                        {
                          "name" : " ' . $c_date . '",
                          "children":[
                          {
                            "name" : " '. $P_posts .'" ,
                            "children":[
                            {
                               "name" : "Likes",
                               "size" : '. $like_count .'
                            },
                            {
                               "name" : "Comments",
                               "size" :' . $comment_count .'
                            },
                            {
                               "name" : "Shares",
                               "size" : ' . $share_count .'
                            }

                            ]
                        }   ';
                $json_file .=$temp_json_file;

            }

        }
        // the date is empty meaning it is the first post 
        else {

          // put the date
          $P_date     = $c_date;
          $P_likes 		= $like_count;
          $P_posts 		= 1;
          $P_shares 	= $share_count;
          $P_comments = $comment_count;


           // make the json file
           $temp_json_file = '
                    {
                     "name" : " ' . $c_date . '",
                     "children":[
                        {
                            "name" : " '. $P_posts .'" ,
                            "children":[
                            {
                               "name" : "Likes",
                               "size" : '. $like_count .'
                            },
                            {
                               "name" : "Comments",
                               "size" :' . $comment_count .'
                            },
                            {
                               "name" : "Shares",
                               "size" : ' . $share_count .'
                            }

                            ]
                        }   ';
      // increment the json file
       $json_file .= $temp_json_file;

        }
} // end of foreach


  // finalize the json file
  $temp_json_file = ' ] }    ]
 } ';

  $json_file .=$temp_json_file;
  //echo $json_file;

    $myFile = "data.json";
     $fh = fopen($myFile, 'w') or die("can't open file");
    // $stringData = json_encode($json_file);
     fwrite($fh, $json_file);

} // end of function





function show_post_details(){
include("connect.php");

  // query statement
  $sql = "select * from days";

  // fetech result 
  $result = $conn->query($sql);

  // if any result 
  if ($result->num_rows > 0) { 

      echo "<table  width=100px>
      <tr>
      <th> Day </th> <th> Posts </th> <th> Likes </th> <th> Comments </th> <th> Shares</th>
      </tr>
      ";

      while($row = $result->fetch_assoc()) {
      
      echo "
      <tr>
      <td> '$row[date]' </td>
      <td> '$row[posts]' </td>
      <td> '$row[likes]' </td>
      <td> '$row[comments]' </td>
      <td> '$row[shares]' </td>
      </tr>
      ";
        

    }
    echo "</table>";

} else {
    echo "0 results";
}

}


function run_db_query($sql){
include("connect.php");

if (mysqli_query($conn, $sql)) {
    //echo "done";
    } else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

}



}
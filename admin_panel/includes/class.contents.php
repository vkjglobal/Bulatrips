<?php

/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for CMS
   Programmer	::> Soumya
   Date		::> 15-7-23
   DESCRIPTION::::>>>>
   This code used to manage CMS
 *****************************************************************************/
include_once "class.DbAction.php";
class Contents extends DbAction
{

  public function __construct()
  {
    $this->db = new DbAction();
  }

  public function getListAboutUs($id)
  {
    $tableName = "about";
    $result = $this->db->selectById($tableName, $id);
    return $result;
  }
  public function updateAbout($title, $imgnewfile, $content, $id)
  {
    $tableName = "about"; //cms table name
    $result =   $this->db->updateAboutDb($tableName, $id, $title, $imgnewfile, $content);
    return $result;
  }
  public function getListNewsletters()
  {
    $tableName = "newsletter";
    $status = 1;
    $result = $this->db->selectBystatus($tableName, $status);
    return $result;
  }
  public function getListNewsletters_New()
  {
    $tableName = "newsletter_new";
    $result = $this->db->selectTravellers($tableName);
    return $result;
  }
  //insert newsletter 
  public function insNewsLetter($title, $subject, $description)
  {
    $tableName = "newsletter_new";
    $params = ['title' => $title, 'subject' => $subject, 'content' => $description];
    //print_r($params);exit;
    $result =   $this->db->insertInto($tableName, $params);
    return $result;
  }
  public function getListNews($id)
  {
    $tableName = "newsletter_new";
    $result = $this->db->selectById($tableName, $id);
    return $result;
  }
  public function updateNews($title, $subject, $description, $id)
  {
    $tableName = "newsletter_new"; //cms table name
    $result =   $this->db->updateNewsDb($tableName, $id, $title, $subject, $description);
    return $result;
  }
  //delete newsletter

  public function DelNews($id)
  {
    $tableName  = "newsletter_new";
    $result    = $this->db->deleteData($tableName, $id);
    return $result;
  }
  public function sendMAil($subject, $message, $email)
  {

    // Send the email using a library or your preferred method
    //==============================================
    $to = $email;
    //subject = "newsletter
    $message = "Please click the following link to reset your password: " . $resetLink;
    $headers = 'From: sooraj@reubrodesigns.com' . "\r\n" .
      'Reply-To: no-reply@bulatrips.com' . "\r\n" .
      'X-Mailer: PHP/' . phpversion();
    /*   if (mail('no-reply@bulatrips.com',$subject, $message, $headers)) {
                echo "success";
          // echo 'Email sent successfully.';
            } else {
           //echo 'Failed to send email.';
           echo "err";
            } */

    // echo 'error14';exit; //failed to send email
    echo 'error15';
    exit; //femail sent successfully
    //=================================================
  }
  public function insHomeBanner($first_title, $second_title, $image)
  {
    //echo "ggg";exit;
    $tableName = "home_banner"; //cms table name
    $params = ['first_title' => $first_title, 'second_title' => $second_title, 'image' => $image];
    // print_r($params);exit;
    $result =   $this->db->insertInto($tableName, $params);
    //print_r($result);exit;
    return $result;
  }
  public function insHomeVideo($title, $description, $video)
  {

    $tableName = "videos"; //cms table name
    $params = ['title' => $title, 'description' => $description, 'video' => $video];
    // print_r($params);exit;
    $result =   $this->db->insertInto($tableName, $params);
    //print_r($result);exit;
    return $result;
  }
  public function getHomepageSliderEdit($id)
  {
    $tableName = "home_banner";
    $result = $this->db->selectById($tableName, $id);
    return $result;
  }
  public function getListHomeBanner()
  {
    $tableName = "home_banner";
    $result = $this->db->selectTravellers($tableName);
    return $result;
  }
  public function getListHomeVideo()
  {
    $tableName = "videos";
    $result = $this->db->selectTravellers($tableName);
    return $result;
  }
  public function updateBannerHome($firstEdit, $secondEdit, $bannerImageEdit, $homeId)
  {
    $tableName = "home_banner"; //cms table name

    $result =   $this->db->updateHomeBanner($tableName, $homeId, $firstEdit, $secondEdit, $bannerImageEdit);
    return $result;
  }
  public function updateVideoHome($firstEdit, $secondEdit, $bannerImageEdit, $homeId)
  {
    $tableName = "videos"; //cms table name

    $result =   $this->db->updateHomeVideo($tableName, $homeId, $firstEdit, $secondEdit, $bannerImageEdit);
    return $result;
  }
  public function DelHomeImages($pid)
  {
    $tableName = "home_banner"; //cms table name

    $result =   $this->db->deleteData($tableName, $pid);
    return $result;
  }
  public function DelHomeVideo($pid)
  {
    $tableName = "videos"; //cms table name

    $result =   $this->db->deleteData($tableName, $pid);
    return $result;
  }
}

<?php

include_once("Mail.php");
include_once("Mail_mime.php");

date_default_timezone_set('Asia/Kolkata');

// Function to send mail using Gmail

function sendMail($to, $subject = "Hey! This is Wisher", $body = "Sample Mail Body for Mail", $from = "pradeep.wwt@gmail.com") {

    // Constructing the email
    $sender = "Pradeep.wwt@gmail.com";// Your name and email address
    $recipient = $to; // The Recipients name and email address
    $subject = $subject;// Subject for the email
    $html = '<html><body  style="font-family: calibri; font-size:14px" >'.$body.'</body></html>';// HTML version of the email
    $crlf = "\r\n";
    $headers = array('From' => $sender, 'Return-Path' => $sender, 'Subject' => $subject);

    // Creating the Mime message
    $mime = new Mail_mime($crlf);

    // Setting the body of the email
    $mime->setHTMLBody($html);

    $body = $mime->get();
    $headers = $mime->headers($headers);

    $smtp = Mail::factory('smtp', array(
            'host' => 'ssl://smtp.gmail.com',
            'port' => '465',
            'auth' => true,
            'username' => 'pradeep.wwt@gmail.com',
            'password' => 'Hello1497'
        ));

    if (PEAR::isError($smtp)) {
       
        echo("<p>" . $smtp->getMessage() . "</p>");
    }
    
   
    $mail = $smtp->send($to, $headers, $body);
    
    if (PEAR::isError($mail)) {
        echo $mail->getMessage() . "\n" . $mail->getUserInfo() . "\n";
        die();
    } else {
        echo('<p>Message successfully sent!</p>');
    }
}

function dateDiff($date1, $date2)
{
    $date1_ts = strtotime($date1);
    $date2_ts = strtotime($date2);
    $diff = $date2_ts - $date1_ts;
    return round($diff / 86400);
}


// CONFIGS
$IS_TEST = FALSE;
$LOOKUP_WINDOW = 0; // lookup window 0 means same day, 1 means a day before and 7 means within 7 days

$TO_EMAIL = "pradeepsingh.gaur@wwt.com";

$BIRTHDAY_MSG_TEMPLATE = [
    "Happy Birthday {{names}}, have a good one!",
    "Many many happy returns of the day {{names}}, have fun!"
    ];

$ANNI_MSG_TEMPLATE = [
    "{{names}} congratulations on completing {{yrs}} yrs with WWT! thank you for your efforts and creativity.",
    "Congratulations {{names}}! on completing {{yrs}} yrs with WWT, wishing you many years of success and innovation.",
    "Congratulations {{names}}! Today I am looking back to when we first recruited you, since then youguys have come a long way. I am immensely proud of having you people in our team and I hope you will continue to thrive. Congrats on your work anniversary!"
    ];

$NEW_JOINEE_MSG_TEMPLATE = [
    "{{names}} welcome to the team! looking forward to work with you."
    ];
    

// FUNCTIONS
// Reading csv file
function read_data_files()
{
    $array = $fields = array(); $i = 0;
    $handle = @fopen("dates.csv", "r");
    if ($handle) {
        while (($row = fgetcsv($handle, 4096)) !== false) {
            if (empty($fields)) {
                $fields = $row;
                continue;
            }
            foreach ($row as $k=>$value) {
                $array[$i][$fields[$k]] = $value;
            }
            $i++;
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }
    
    return $array;
}

$array = read_data_files();

// function to prepare and send birthday messages
function prepare_and_send_birthday_msgs($array,$birthday_msg_template)
{
    $birthday_message = "";
    $b_array = array();
    global $LOOKUP_WINDOW;
    global $IS_TEST;

    foreach($array as $emp)
    {
        if($emp["DOB"] != '')
        {
            $today = date("Y-m-d");

            $time = strtotime( date("Y") ."-". explode("-",$emp["DOB"])[1] ."-". explode("-",$emp["DOB"])[0]);
            $dob = date('Y-m-d', $time);
          

            if ( (dateDiff($today, $dob) <= $LOOKUP_WINDOW && dateDiff($today, $dob) >= 0) || $IS_TEST)
            {
                array_push($b_array, str_replace (",", "",explode( " ", $emp["Employee"])[0]));
              
            }
            
        }
       
    }

    $b_names = implode(", ", $b_array);
    
    $b_array = array();
    
    for($i = 0; $i < sizeof($birthday_msg_template); $i++)
    {
        $temp_msg = str_replace("{{names}}", $b_names, $birthday_msg_template[$i]);
        array_push($b_array, $temp_msg);
    }
    
    $b_msg = implode("<br/><br/>", $b_array);
    $b_msg = "<h3 style='color:#154c79; font-family: verdana'>Birthday's</h3><hr/>". $b_msg . "<br/>";
    
    $birthday_message = $b_msg;

    if($b_names == "")
    {
        $birthday_message = "";
    }

    return $birthday_message;
}


$b_msg_mail = prepare_and_send_birthday_msgs($array, $BIRTHDAY_MSG_TEMPLATE);
//($b_msg_mail != "")? sendMail("pradeepcitm@gmail.com", "Team Birthdays!", $b_msg_mail): "birthdays do nothing";

//function to prepare and send work anniversary messages
function prepare_and_send_work_anni_msgs($array, $anni_msg_template)
{
    $anni_message = "";
    $a_array = array();
    $yrs_array = array();
    global $LOOKUP_WINDOW;
    global $IS_TEST;

    foreach($array as $emp)
    {
        if($emp["Hire Date"] != '')
        {
            $today = date("Y-m-d");
            $time = strtotime( date("Y") ."-". explode("-", $emp["Hire Date"])[1] ."-". explode("-",$emp["Hire Date"])[0]);
            $jd = date('Y-m-d', $time);
            
          

            if ((dateDiff($today, $jd) <= $LOOKUP_WINDOW && dateDiff($today, $jd) >= 0) || $IS_TEST)
            {
                $hire_date = date('Y-m-d', $time);
                $year_old = date('Y', strtotime($emp["Hire Date"]));
                $year_new = date('Y', strtotime($hire_date));
               
                if(($year_new - $year_old) > 0)
                {
                    array_push($a_array, str_replace (",", "",explode( " ", $emp["Employee"])[0]));
                    array_push($yrs_array, ($year_new - $year_old)); 
                    
                }

              
            }
        }
    }

    
    $a_names = implode(", ", $a_array);
    $a_yrs = implode(", ", $yrs_array);
    
  
    $a_array = array();
    
    for($i = 0; $i < sizeof($anni_msg_template); $i++)
    {
        $temp_msg = str_replace("{{names}}", $a_names, $anni_msg_template[$i]);
        $temp_msg = str_replace("{{yrs}}", $a_yrs, $temp_msg);
        
        array_push($a_array, $temp_msg);
    }
    
    $a_msg = implode("<br/><br/>", $a_array);
    $a_msg = "<h3 style='color:#154c79; font-family: verdana'>Work Anniversaries</h3><hr/>". $a_msg . "<br/>";
    
    $anni_message = $a_msg;

    if($a_names == "")
    {
        $anni_message = "";
    }

    return $anni_message;
}


$a_msg_mail = prepare_and_send_work_anni_msgs($array, $ANNI_MSG_TEMPLATE);
//($a_msg_mail != "")? sendMail("pradeepcitm@gmail.com", "Team Anniversaries!", $a_msg_mail): "Anniversaries do nothing";
//echo $a_msg_mail;


//echo "<br/><br/>";
//function to prepare and send new joinee messages
function prepare_and_send_new_joinee_msgs($array, $new_joinee_msg_template)
{
    $new_joinee_message = "";
    $nj_array = array();
    $yrs_array = array();
    global $LOOKUP_WINDOW;
    global $IS_TEST;

    foreach($array as $emp)
    {
        if($emp["Hire Date"] != '')
        {
        
            $today = date("Y-m-d");
    
            $time = strtotime( explode("-",$emp["Hire Date"])[2] ."-". explode("-",$emp["Hire Date"])[1] ."-". explode("-",$emp["Hire Date"])[0]);
            $jd = date('Y-m-d', $time);
        
            if ((dateDiff($today, $jd) <= $LOOKUP_WINDOW && dateDiff($today, $jd) >= 0) || $IS_TEST)
            {
                array_push($nj_array, str_replace (",", "",explode( " ", $emp["Employee"])[0]));
            }
           
        }
        
    }
    
    $nj_names = implode(", ", $nj_array);
    
    $nj_array = array();
    
    for($i = 0; $i < sizeof($new_joinee_msg_template); $i++)
    {
        $temp_msg = str_replace("{{names}}", $nj_names, $new_joinee_msg_template[$i]);
        array_push($nj_array, $temp_msg);
    }
    
    $nj_msg = implode("<br/><br/>", $nj_array);
    $nj_msg = "<h3 style='color:#154c79; font-family: verdana'>New Joiners</h3><hr/>". $nj_msg . "<br/>";
    
    $new_joinee_message = $nj_msg;
    
    if($nj_names == "")
    {
        $new_joinee_message = "";
    }

    return $new_joinee_message;
}


$nj_msg_mail = prepare_and_send_new_joinee_msgs($array, $NEW_JOINEE_MSG_TEMPLATE);
//($nj_msg_mail != "")? sendMail("pradeepcitm@gmail.com", "Team Anniversaries!", $nj_msg_mail): "Anniversaries do nothing";
//echo $nj_msg_mail;


// combining wishes and sending mail

$mail_body = $b_msg_mail. "<br/><br/>". $a_msg_mail ."<br/><br/>". $nj_msg_mail;

echo $mail_body;
//sendMail("pradeepcitm@gmail.com", "We have some Wishes!", $mail_body);
($mail_body != "<br/><br/><br/><br/>")? sendMail($TO_EMAIL, "We have some Wishes for Today!", $mail_body): "Do nothing";
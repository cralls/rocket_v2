<?php

namespace Rocket\Teamrocket\Model\Cron;

class SendEmails
{

	public function execute()
	{
	    // Log result
	    error_log(date('Y-m-d H:i:s')." - RESEND REQUEST \r\n", 3, '/home/rocketsc/public_html/var/log/tr-email.log');
	    
	    // Get Processing Orders
	    // Add e-mail and name to tr_email for sending in 1 hour
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
	    $connection = $resource->getConnection();
	    $tableName = $resource->getTableName('tr_email');
	    $sql = "select * from " . $tableName . " where sent = 0";
	    $emails = $connection->query($sql);
        
        foreach($emails as $email) {
            // Sign-up E-mail
            $msg = "Congratulations, ".$email['name']."! You've been chosen to be a part of #TEAMROCKET! Welcome to the family!<br><br>
                
Your EXCLUSIVE gear catalog can be accessed here:<br>
<a href='https://www.rocketsciencesports.com/team-portals/teamrocket.html'>https://www.rocketsciencesports.com/team-portals/teamrocket.html</a><br><br>
                
Your personalized discount code is below - it applies to not only #TEAMROCKET exclusive products, but to all non-custom products across www.rocketsciencesports.com as well:<br><br>
                
<b>AMBASSADOR40OFF</b><br><br>
                
<span style='color: red;'>*Please note that orders are processed on the last day of each calendar month, and take 4 weeks for production, not including shipping time.</span><br><br>
                
Remember, you are free to share this link with your team, your family and your friends as you see fit. Spread the love!<br><br>
                
<span style='color: red;'>ADDITIONAL INFORMATION:</span><br><br>
                
<b><u>Social Media</b></u><br><br>
                
https://www.facebook.com/rocketsciencesports<br>
https://www.instagram.com/rocketsciencesports<br>
https://twitter.com/rocketscisports<br>
https://www.pinterest.com/rocketsciences<br>
https://www.youtube.com/c/RocketScienceSports<br><br>
                
<b><u>Logos</b></u><br><br>
                
Our logo stylesheet can be accessed below.<br><br>
                
https://www.dropbox.com/s/476059bpl9ahcet/RSS_LOGO%20STYLE%20SHEET%202021_0824.pdf?dl=0<br><br>
                
<b><u>Hashtags</b></u><br><br>
                
If you would like to tag us on social media, don't forget the following hashtags!<br><br>
                
#rocketsciencesports<br>
#teamrocket<br>
#racewithattitude<br><br>
                
                
If you have any questions or concerns, please feel free to email us at sales@rocketsciencesports.com and a member of our sales team will get back to you ASAP!<br><br>
                
Don't forget to #RACEWITHATTITUDE!<br>
The Rocket Fam";
            
            $to = [$email['email']];
            $nemail = new \Zend_Mail();
            $nemail->setSubject("Team Rocket Signup");
            $nemail->setBodyHtml($msg);
            $nemail->setFrom('tamara@rocketsciencesports.com', 'Rocket Science Sports');
            $nemail->addTo($to);
            try {
                $nemail->send();
                // Log successful send if needed
                error_log(date('Y-m-d H:i:s') . " - Email sent successfully for ID: " . $email['id'] . "\r\n", 3, '/home/rocketsc/public_html/var/log/tr-email.log');
                
                // Save as sent
                $sql = "UPDATE " . $tableName . " SET sent = 1 WHERE id = " . $email['id'];
                $connection->query($sql);
            } catch (\Exception $e) {
                // Log detailed error information
                error_log(date('Y-m-d H:i:s') . " - ERROR sending email for ID " . $email['id'] . ": " . $e->getMessage() . "\r\n", 3, '/home/rocketsc/public_html/var/log/tr-email.log');
            }
            
            // Save as sent
            $sql = "update " . $tableName . " set sent = 1 where id = ".$email['id'];
            $connection->query($sql);
        }
        
        
        
        
	}
	
}

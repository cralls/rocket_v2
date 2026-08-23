<?php

namespace Rocket\Teamrocket\Controller\Index;

use Magento\Framework\Controller\ResultFactory;    

class Index extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        
    
        
	// Add e-mail and name to tr_email for sending in 1 hour
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection = $resource->getConnection();
    $tableName = $resource->getTableName('tr_email');
    $sql = "Insert Into " . $tableName . " (email, name) Values ('".$_POST['email']."', '".$_POST['name']."')";
    if(strpos($_SERVER['HTTP_REFERER'], 'team-rocket-signup')) $connection->query($sql);

	// Mail to tamara@rocketsciencesports.com

	$msg = "<b>Team Rocket Signup!</b><br><br>

Name: ".$_POST['name']."<br>
Team Name: ".$_POST['team-name']."<br>
Email: ".$_POST['email']."<br>
DOB: ".$_POST['dob']."<br>
Your Sport(s): ";
	if(isset($_POST['yoursport'])) {
        foreach($_POST['yoursport'] as $key => $sport) {
        	$msg .= $sport;
        	if(isset($_POST['yoursport'][$key+1])) $msg .= ", ";
        }
	}
$msg .= "<br>Other Sport: ".$_POST['other-sport']."<br>
Facebook (URL): ".$_POST['facebook']."<br>
Instagram (URL): ".$_POST['instagram']."<br>
Twitter (URL): ".$_POST['twitter']."<br>
Pinterest (URL): ".$_POST['pinterest']."<br>
Other Social: ".$_POST['othersocial']."<br><br>";

 $to = ['tamara@rocketsciencesports.com'];
    $nemail = new \Zend_Mail();
    $nemail->setSubject("New Team Rocket Signup");
    $nemail->setBodyHtml($msg);
    $nemail->setFrom('tamara@rocketsciencesports.com', 'Rocket Science Sports');
    $nemail->addTo($to);
    if(strpos($_SERVER['HTTP_REFERER'], 'team-rocket-signup'))  $nemail->send();

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }

}

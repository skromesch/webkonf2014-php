<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ContactService\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\JsonModel as JsonModel;
use Aws\DynamoDb\Enum\Type;
use Aws\DynamoDb\Enum\AttributeAction;


class ContactController extends AbstractRestfulController
{
    protected $collectionOptions = array('GET');
    protected $resourceOptions = array('GET','POST','PUT','DELETE');
    protected $tableName = "Contacts";
    
    public function getList()
    {
        try{
            $client = $this->_getDynamoDbClient();
            $response = $client->scan(array(
                "TableName" => $this->tableName
            ));
            return new JsonModel($response);

        }catch(\Exception $ex){
            return $this->_apiException($ex);
        }        
    }     

    public function get($id)
    {
        try{
            $client = $this->_getDynamoDbClient();
            $response = $client->getItem(array(
                "TableName" => $this->tableName,
                 "Key" => array(
                    "id" => array( Type::STRING => $id )
                    )
            ));
            if(is_null($response["Item"])){
                return $this->_apiError(404,"Not found:".$id);

            }
            return new JsonModel($response["Item"]);

        }catch(\Exception $ex){
            return $this->_apiException($ex);
        }
    }     

    public function delete($id){
        try{
            $client = $this->_getDynamoDbClient();
            $response = $client->deleteItem(array(
                'TableName' => $this->tableName,
                'Key' => array(
                    'id' => array(
                        Type::STRING => $id
                    )
                )
            ));
            return new JsonModel(array("message" => "OK"));

        }catch(\Exception $ex){
            return $this->_apiException($ex);
        }
    }

    public function update($id,$data){
        try{
            $client = $this->_getDynamoDbClient();
            if(!isset($data["name"])){
                return $this->_apiError(404,"Missing parameter name");
            }
            if(!isset($data["email"])){
                return $this->_apiError(404,"Missing parameter email");
            }
            if(!isset($data["age"])){
                return $this->_apiError(404,"Missing parameter age");
            }
            $response = $client->updateItem(array(
                "TableName" => $this->tableName,
                    "Key" => array(
                        "id" => array(
                            Type::STRING => $id
                        )
                    ),
                    "AttributeUpdates" => array(
                        "name" => array(
                            "Action" => AttributeAction::PUT,
                            "Value" => array(
                                Type::STRING => $data["name"]
                            )
                        ),
                        "age" => array(
                            "Action" => AttributeAction::PUT,
                            "Value" => array(
                                Type::NUMBER => $data["age"]
                            )
                        ),
                        "email" => array(
                            "Action" => AttributeAction::PUT,
                            "Value" => array(
                                Type::STRING => $data["email"]
                            )
                        ),
                    )
            ));
            return new JsonModel(array("message" => "OK"));

        }catch(\Exception $ex){
            return $this->_apiException($ex);
        }
    }
    public function create($data){
        try{
            $client = $this->_getDynamoDbClient();
            if(!isset($data["name"])){
                return $this->_apiError(404,"Missing parameter name");
            }
            if(!isset($data["email"])){
                return $this->_apiError(404,"Missing parameter email");
            }
            if(!isset($data["age"])){
                return $this->_apiError(404,"Missing parameter age");
            }
            $response = $client->putItem(array(
                "TableName" => $this->tableName, 
                "Item" => array(
                    "id"      => array(Type::STRING => $this->uuid()),
                    "name"    => array( Type::STRING      => $data["name"] ),
                    "age"     => array( Type::NUMBER      => $data["age"] ),
                    "email"    => array( Type::STRING      => $data["email"] )
                    )
            ));
            return new JsonModel(array("message" => "OK"));

        }catch(\Exception $ex){
            return $this->_apiException($ex);
        }

    }

    protected function _getDynamoDbClient(){
            $aws    = $this->getServiceLocator()->get('aws');
            $client = $aws->get('dynamodb');
            return $client;

    }

    protected function _apiException(\Exception $ex){
            return $this->_apiError(500,$ex->getMessage());
    }

    protected function _apiError($statusCode,$message){
            $response = $this->getResponse();
            $response->setStatusCode($statusCode);
            return new JsonModel(array("error" => $message));

    }
    protected function _getOptions() { 
        if ($this->params()->fromRoute('id', false)) { // we have an ID, return specific item return 
            return $this->resourceOptions; 
        }   
        return $this->collectionOptions; 
    } 
    public function options() { 
        $response = $this->getResponse(); 
        // If in Options Array, Allow 
        $response->getHeaders() 
            ->addHeaderLine('Allow', implode(',', $this->_getOptions())); 
        // Return Response 
        return $response; 
    }    
    public function setEventManager(EventManagerInterface $events) { 
        parent::setEventManager($events); 
        $events->attach('dispatch',Array($this,'checkOptions'),10); 
    }    
    public function checkOptions($e) { 
        if (in_array($e->getRequest()->getMethod(), $this->_getOptions())) { 
            // Method Allowed, Nothing to Do 
            return; 
        }  
        // Method Not Allowed 
        $response = $this->getResponse(); 
        $response->setStatusCode(405); 
        return $response;    
     }
    public function uuid() {

        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', rand(50000));

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) {
          $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }

        // Calculate hash value
        $hash = md5($nstr . microtime());

        return sprintf('%08s-%04s-%04x-%04x-%12s',

          // 32 bits for "time_low"
          substr($hash, 0, 8),

          // 16 bits for "time_mid"
          substr($hash, 8, 4),

          // 16 bits for "time_hi_and_version",
          // four most significant bits holds version number 3
          (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

          // 16 bits, 8 bits for "clk_seq_hi_res",
          // 8 bits for "clk_seq_low",
          // two most significant bits holds zero and one for variant DCE1.1
          (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

          // 48 bits for "node"
          substr($hash, 20, 12)
        );
      }

}
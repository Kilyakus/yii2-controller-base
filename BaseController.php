<?php
namespace kilyakus\controller;

use Yii;
use yii\helpers\Url;

class BaseController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    public $rootActions = [];
    public $error = null;
    public $transferClasses = [];

    public function flash($type, $message)
    {
        Yii::$app->getSession()->setFlash($type=='error'?'danger':$type, $message);
    }

    public function back()
    {
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function setReturnUrl($url = null)
    {
        Yii::$app->getSession()->set($this->module->id.'_return', $url ? Url::to($url) : Url::current());
    }

    public function getReturnUrl($defaultUrl = null)
    {
        return Yii::$app->getSession()->get($this->module->id.'_return', $defaultUrl ? Url::to($defaultUrl) : Url::to('/admin/'.$this->module->id));
    }

    public function formatResponse($success = '', $back = true)
    {
        if(Yii::$app->request->isAjax){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if($this->error){
                return ['result' => 'error', 'error' => $this->error];
            } else {
                $response = ['result' => 'success'];
                if($success) {
                    if(is_array($success)){
                        $response = array_merge(['result' => 'success'], $success);
                    } else {
                        $response = array_merge(['result' => 'success'], ['message' => $success]);
                    }
                }
                return $response;
            }
        }
        else{
            if($this->error){
                $this->flash('error', $this->error);
            } else {
                if(is_array($success) && isset($success['message'])){
                    $this->flash('success', $success['message']);
                }
                elseif(is_string($success)){
                    $this->flash('success', $success);
                }
            }
            return $back ? $this->back() : $this->refresh();
        }
    }

    public function __get($name){
       if (array_key_exists($name, $this->transferClasses))
           return $this->transferClasses[$name];

       return parent::__get($name);
    }

    public function __set($name, $value){
       if (array_key_exists($name, $this->transferClasses))
           $this->transferClasses[$name] = $value;

       else parent::__set($name, $value);
    }

    public function put($attribute)
    {
        $this->transferClasses[$attribute] = null;
        $this->__set($attribute, null);
    }
}

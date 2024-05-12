<?php
namespace Core\Services;

use Silet\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionHelper{
	/**
     * enable transaction in an application, make sure to call this function in top level service
     * @param Application $app
     * return void
     */     
	public static function enableTransaction(Application &$app){
	    if(isset($app['use_transaction']) && $app['use_transaction']==TRUE){
	       $app['use_transaction_count'] = $app['use_transaction_count'] + 1; 
	       return;
        }
		$app['use_transaction'] = TRUE;
		$app['db_transaction'] = NULL;
        $app['use_transaction_count'] = 1;	
	}
    /**
     * commit the transaction to db
     * @param Application $app
     * return boolean
     */     
	public static function commitTransaction(Application &$app){
		if(isset($app['use_transaction']) && $app['use_transaction']==TRUE){
		   $app['use_transaction_count'] = $app['use_transaction_count'] - 1;
			if($app['db_transaction']!=NULL && $app['use_transaction_count']<=0){
				$ret = $app['db_transaction']->commit();
                $app['use_transaction'] = FALSE;
                $app['db_transaction'] = NULL;
                $app['use_transaction_count'] = 0;
                return $ret;
			}
               
		}
		return TRUE;
	}
	
	/**
     * reject the transaction to db
     * @param Application $app
     * return boolean
     */     
	public static function rollbackTransaction(Application &$app){
		if(isset($app['use_transaction']) && $app['use_transaction']==TRUE){		   
			if($app['db_transaction']!=NULL && $app['use_transaction_count']>=0){
				$ret = $app['db_transaction']->rollback();
                $app['use_transaction'] = FALSE;
                $app['db_transaction'] = NULL;
                $app['use_transaction_count'] = 0;
                return $ret;
			}
               
		}
		return TRUE;
	}
}

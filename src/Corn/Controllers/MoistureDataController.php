<?php
namespace Corn\Controllers;

use Silet\Application;
use Symfony\Component\HttpFoundation\Request;
use Corn\Entities\SaveMoistureDataForm;
use Corn\Entities\SearchMoistureDataForm;

class MoistureDataController
{
	public static function save(Application $app, Request $request)
    {
		$payload = $app['payload'];
        $userId = $payload->userId;
        
        $form = new SaveMoistureDataForm();
        $form->bindRequest($request);
        // var_dump($form);
        $moistureService = $app['moistureDataService']();
        $ret = $moistureService->save($userId, $form);
        if ($ret == FALSE) {
            return $app->json([
                'status' => 0,
                'message' => 'Failed to save moisture deta'
            ]);
        } else {
            return $app->json([
                'status' => 1,
                'message' => $ret
            ]);
        }
    }
    
    public static function search(Application $app, Request $request){		
		
		$payload = $app['payload'];
        $userId = $payload->userId;
        
        $form = new SearchMoistureDataForm();
        //$form->bindRequest($request);
        $form->fileName = $request->get('fileName');
        $form->locationName = $request->get('locationName');
        $form->recordDate = $request->get('recordDate');
        // var_dump($form);
        $moistureService = $app['moistureDataService']();
        $ret = $moistureService->search($userId, $form);
        if ($ret == FALSE) {
            return $app->json([
                'status' => 0,
                'message' => 'Failed to search moisture deta'
            ]);
        } else {
            return $app->json([
                'status' => 1,
                'message' => $ret
            ]);
        }
	}
}

<?php
class ServiceProvider_ApiAi extends Extension_ServiceProvider implements IServiceProvider_HttpRequestSigner {
	const ID = 'wgm.apiai.service.provider';
	
	function renderConfigForm(Model_ConnectedAccount $account) {
		$tpl = DevblocksPlatform::getTemplateService();
		$active_worker = CerberusApplication::getActiveWorker();
		
		$params = $account->decryptParams($active_worker);
		$tpl->assign('params', $params);
		
		$tpl->display('devblocks:wgm.apiai::provider/edit_params.tpl');
	}
	
	function saveConfigForm(Model_ConnectedAccount $account, array &$params) {
		@$edit_params = DevblocksPlatform::importGPC($_POST['params'], 'array', array());
		
		$active_worker = CerberusApplication::getActiveWorker();
		
		if(!isset($edit_params['access_token']) || empty($edit_params['access_token']))
			return "The 'Access Token' is required.";
		
		// Test the credentials
		
		$url = sprintf('https://api.api.ai/v1/query?v=20150910&lang=en&query=weather&sessionId=0000000000');
		
		$ch = DevblocksPlatform::curlInit($url);
		
		$headers = array(
			'Authorization: Bearer ' . $edit_params['access_token'],
		);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$out = DevblocksPlatform::curlExec($ch);
		
		$json = json_decode($out, true);
		
		error_log($out);
		
		$status_code = $json['status']['code'];
		
		if(200 != $status_code)
			return "Failed to verify the given API access token.";
		
		foreach($edit_params as $k => $v)
			$params[$k] = $v;
		
		return true;
	}
	
	function authenticateHttpRequest(Model_ConnectedAccount $account, &$ch, &$verb, &$url, &$body, &$headers) {
		$credentials = $account->decryptParams();
		
		if(!isset($credentials['access_token']))
			return false;
			
		$headers[] = sprintf('Authorization: Bearer %s', $credentials['access_token']);
		return true;
	}
}
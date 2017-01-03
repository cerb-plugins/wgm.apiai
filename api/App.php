<?php
class ServiceProvider_ApiAi extends Extension_ServiceProvider implements IServiceProvider_HttpRequestSigner, IServiceProvider_Popup {
	const ID = 'wgm.apiai.service.provider';
	
	function renderPopup() {
		$this->_renderPopupAuthForm();
	}
	
	function renderAuthForm() {
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'], 'string', '');
		
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->assign('view_id', $view_id);
		
		$tpl->display('devblocks:wgm.apiai::provider/setup.tpl');
	}
	
	function saveAuthFormAndReturnJson() {
		@$params = DevblocksPlatform::importGPC($_POST['params'], 'array', array());
		
		$active_worker = CerberusApplication::getActiveWorker();
		
		if(!isset($params['agent_name']) || empty($params['agent_name']))
			return json_encode(array('status' => false, 'error' => "The 'Agent Name' is required."));
		
		if(!isset($params['access_token']) || empty($params['access_token']))
			return json_encode(array('status' => false, 'error' => "The 'Access Token' is required."));
		
		// Test the credentials
		
		$url = sprintf('https://api.api.ai/v1/query?v=20150910&lang=en&query=weather');
		
		
		$ch = DevblocksPlatform::curlInit($url);
		
		$headers = array(
			'Authorization: Bearer ' . $params['access_token'],
		);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$out = DevblocksPlatform::curlExec($ch);
		
		$json = json_decode($out, true);
		
		$status_code = $json['status']['code'];
		
		
		if(200 != $status_code)
			return json_encode(array('status' => false, 'error' => "Failed to verify the given API access token."));
		
		$id = DAO_ConnectedAccount::create(array(
			DAO_ConnectedAccount::NAME => sprintf('Api.ai: %s', $params['agent_name']),
			DAO_ConnectedAccount::EXTENSION_ID => ServiceProvider_ApiAi::ID,
			DAO_ConnectedAccount::OWNER_CONTEXT => CerberusContexts::CONTEXT_WORKER,
			DAO_ConnectedAccount::OWNER_CONTEXT_ID => $active_worker->id,
		));
		
		DAO_ConnectedAccount::setAndEncryptParams($id, $params);
		
		return json_encode(array('status' => true, 'id' => $id));
	}
	
	function authenticateHttpRequest(Model_ConnectedAccount $account, &$ch, &$verb, &$url, &$body, &$headers) {
		$credentials = $account->decryptParams();
		
		if(!isset($credentials['access_token']))
			return false;
			
		$headers[] = sprintf('Authorization: Bearer %s', $credentials['access_token']);
		return true;
	}
}
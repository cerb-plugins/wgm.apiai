<h2>Connect to Api.ai</h2>

<form action="javascript:;" method="post" id="frmSetup" onsubmit="return false;">
<input type="hidden" name="c" value="profiles">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="connected_account">
<input type="hidden" name="action" value="saveAuthFormJson">
<input type="hidden" name="ext_id" value="{ServiceProvider_ApiAi::ID}">
<input type="hidden" name="_csrf_token" value="{$session.csrf_token}">

<fieldset style="margin-top:5px;">
	<legend>API Credentials</legend>
	
	<b>Agent Name:</b><br>
	<input type="text" name="params[agent_name]" value="{$params.agent_name}" size="64" spellcheck="false"><br>
	<br>
	
	<b>Access Token:</b><br>
	<input type="text" name="params[access_token]" value="{$params.access_token}" size="64" spellcheck="false"><br>
	<br>
	
	<div>
		<div class="status" style="display:inline-block;"></div>
	</div>

	<button type="button" class="submit"><span class="glyphicons glyphicons-circle-ok" style="color:rgb(0,180,0);"></span> {'common.save_changes'|devblocks_translate|capitalize}</button>	
</fieldset>

</form>

<script type="text/javascript">
$(function() {
	var $frm = $('#frmSetup');
	
	$frm.find('BUTTON.submit')
		.click(function(e) {
			genericAjaxPost($frm,'',null,function(json) {
				if(false == json || false == json.status) {
					var error = 'An unexpected error occurred.';
					
					if(json.error)
						error = json.error;
						
					Devblocks.showError('#frmSetup div.status', error);
					
				} else {
					window.opener.genericAjaxGet('view{$view_id}', 'c=internal&a=viewRefresh&id={$view_id}');
					window.close();
				}
			});
		})
	;
});
</script>
<div class="content_norm_top"></div>	
<div class="content_norm_mid">
	
		<?php if (!empty($phone) && ($phone_verify == "verified")) { ?>
		
		<h3>Your Mobile Number</h3>
		<form method="post" action="<?= base_url()."settings/mobile"; ?>">
		<table border="0" cellpadding="0" cellspacing="0">
	    <tr>		
			<td>Phone:</td>
			<td>+1 <?= $phone ?><input type="hidden" name="phone" value="<?= $phone ?>" /></td>
		</tr>
		<tr>
			<td>Search:</td>
			<td><?= form_checkbox($phone_search); ?> Allow others to find you by phone number</td>
		</tr>
		<tr>
			<td>Text Updates:</td>
			<td><?= form_dropdown('phone_active', $phone_active_array, $phone_active); ?></td>
		</tr>
		<tr>
			<td><a href="<?= base_url()."settings/mobile_delete" ?>">Delete this number</a></td>
		</tr>
	    <tr>		
			<td colspan="2">
				<input type="submit" value="Save" />
			</td>
	  	</tr>	
		</table>
		
		<h3>Send Text Messages</h3>
		<p>Send status updates with your cell phone to:</p>
		<h3>+1 <?= $this->config->item('twilio_receive_number') ?> (US)</h3>	
	
		<?php } elseif (!empty($phone) && ($phone_verify != "verified")) { ?>
	
		<h3>Your Mobile Number</h3>
	
		<p>Phone: +1 <?= $phone ?></p>
		
		<h3>Needs To Be Verified</h3>	
		<p>Send a text message to:</p>
		
		<h3>+1 <?= $this->config->item('twilio_receive_number'); ?></h3>
	
		<p>With this word as the message:</p>
		<h3><?= $phone_verify; ?></h3>
		
		<p>Or <a href="<?= base_url()."settings/mobile_delete" ?>">delete this number</a></p>
		
		<?php } else { ?>
		
		<h3>Add Mobile Number</h3>
		<form method="post" action="<?= base_url()."settings/mobile"; ?>">
		<table border="0" cellpadding="0" cellspacing="0">
	    <tr>		
			<td>Phone:</td>
			<td>+1 <input type="text" name="phone" size="18" value="<?= set_value('phone', $phone) ?>"></td>
		</tr>
		<tr>
			<td>Search:</td>
			<td><?= form_checkbox($phone_search); ?> Allow others to find you by phone number</td>
		</tr>
		<tr>
			<td>Text Updates:</td>
			<td><?= form_dropdown('phone_active', $phone_active_array, $phone_active); ?></td>
		</tr>
	    <tr>		
			<td colspan="2"><input type="submit" value="Add" /></td>
	  	</tr>	
		</table>		
		<?php } ?>
		</form>

</div>
<div class="content_norm_bot"></div>
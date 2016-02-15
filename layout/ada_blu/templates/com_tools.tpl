<div class="ui small inverted menu ada">
 <div class="item">
  	<i class="users circular inverted icon"></i><i18n>chi è online</i18n>
	<span>
		<template_field class="template_field" name="chat_users">chat_users</template_field>
	</span>
 </div>
 <div class="item">
 	<i class="mail circular inverted icon"></i>
	<a href="#" onclick="openMessenger(&quot;../comunica/list_messages.php&quot;,800,600);">messaggi per te</a>
	<span>
		<template_field class="template_field" name="messages">messages</template_field>
	</span>  
 </div>
 <div class="item">
 	<i class="calendar circular inverted icon"></i>
 	<a href="#" onclick="openMessenger(&quot;../comunica/list_events.php&quot;,800,600);">i tuoi appuntamenti</a>
	<span>
		 <template_field class="template_field" name="agenda">agenda</template_field>
	</span>  
 </div>
</div>

<div class="ui grid">

	<div class="ui computer tablet only row borderless teal inverted ada  menu <template_field class="template_field" name="isVertical">isVertical</template_field>">
	   <template_field class="template_field" name="adamenu">adamenu</template_field> 
	</div>
	
	<div class="ui mobile only row ada menubutton">
		<div class="menubutton-container">
			<a class="ui labeled icon teal down small fluid button" onclick="javascript:$j('#mobilesidebar').sidebar('toggle');">
		        <i class="reorder icon"></i><i18n>Menu</i18n></a>
		</div>
	</div>

	<div id="mobilesidebar" class="ui left thin sidebar">
		<!-- mobile menu container -->
	    <div class="ui mobile only row borderless teal inverted vertical ada menu <template_field class="template_field" name="isVertical">isVertical</template_field>">
	    </div>
	    <!-- /mobile menu container -->
    </div>
</div>

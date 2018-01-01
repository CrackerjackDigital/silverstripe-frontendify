<% uncached %>
<script type="text/x-tmpl" class="frontendify-add-inline-template ss-gridfield-add-inline-template">
	<tr class="ss-gridfield-item frontendify-inline-new ss-gridfield-inline-new">
		<% loop $Me %>
			<% if $IsActions %>
				<td $Attributes>
					<button class="ss-gridfield-delete-inline gridfield-button-delete ss-ui-button ui-icon btn-icon-cross-circle" data-icon="cross-circle"></button>
				</td>
			<% else %>
				<td $Attributes>$Content</td>
			<% end_if %>
		<% end_loop %>
	</tr>
</script>
<% end_cached %>
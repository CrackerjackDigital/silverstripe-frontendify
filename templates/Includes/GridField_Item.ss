<tr class="ss-gridfield-item ss-gridfield-{$EvenOdd} $FirstLast" data-id="$ID">
	<% if $GridField.ExtraColumnsCount %>
	<% loop $Fields %>
	<td>Sping $Value</td>
	<% end_loop %>
	<td colspan="$GridField.ExtraColumnsCount" class="ss-gridfield-last"></td>
	<% else %>
	<% loop $Fields %>
		<td class="<% if FirstLast %>ss-gridfield-{$FirstLast}<% end_if %>">Sping $Value</td>
	<% end_loop %>
	<% end_if %>
</tr>

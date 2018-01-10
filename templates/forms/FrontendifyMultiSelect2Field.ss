<select $AttributesHTML>
	<% loop $Options %>
	<option value="$Value.XML" class="<% if $Conflict %>conflict<% end_if %>"
	<% if $Selected %> selected="selected"<% end_if %>
	<% if $Disabled %> disabled="disabled"<% end_if %>>
	<% if $Title.exists %>$Title.XML<% else %>&nbsp;<% end_if %>
	</option>
	<% end_loop %>
</select>

<script type="text/template" data-grid="product" data-template="results">

	<% _.each(results, function(r) { %>

		<tr data-grid-row>
			<td><input content="id" input data-grid-checkbox="" name="entries[]" type="checkbox" value="<%= r.id %>"></td>
			<td><a href="<%= r.edit_uri %>"><img src="<%= r.imgurl %>" style="max-height:50px;max-width:50px;"></a></td>
			<td><a href="<%= r.edit_uri %>"><%= r.product_title %></a></td>
			<td><a href="<%= r.edit_uri %>"><%= r.id %></a></td>
			<td><a href="<%= r.edit_uri %>"><%= r.slug %></a></td>
			<td><%= r.code %></td>
			<td><%= r.ean %></td>
			<td><%= r.weight %></td>
			<td><%= r.stock %></td>
			<td><%= r.created_at %></td>
		</tr>

	<% }); %>

</script>

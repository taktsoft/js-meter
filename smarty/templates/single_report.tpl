<h2><a id="{$report.id}" href="#">{$report.title}</a></h2>

<div class="profile">
	<p>Aggregated runtime: {$report.aggregated_runtime} ms</p>
	
	<h3>Functions</h3>
	
	<img src="http://chart.apis.google.com/chart?cht=p&amp;chd=t:{$report.chart.data}&amp;chl= {$report.chart.labels}&amp;chs=700x250&amp;chco=224499&amp;" alt="chart image" title="chart image" class="chart" />
	
	<table>
		<thead>
			<tr>
				<th class="first">Event</th>
				<th>Function</th>
				<th>Query</th>
				<th>Args</th>
				<th>Selector</th>
				<th>Context</th>
				<th colspan="2">Runtime</th>
				<th class="last">Details</th>
			</tr>
		</thead>
		
		<tbody>
			{section name=func loop=$report.functions}
				{if $report.functions[func].details}
				<tr class="{cycle values="odd, even"} detail_toggle" id="detf_{$report.functions[func].id}">
				{else}
				<tr class="{cycle values="odd, even"}">
				{/if}
					<td class="first">{$report.functions[func].event}</td>
					<td colspan="5">{$report.functions[func].functionName}</td>
					<td class="numerical">{$report.functions[func].runtime} ms</td>
					<td class="numerical">{$report.functions[func].percent}</td>
					<td class="last">
						{if $report.functions[func].details}
							<img height="16px" width="16px" class="ui-icon ui-icon-folder-collapsed" />
						{/if}
					</td>
				</tr>
				
				{section name=func_detail loop=$report.functions[func].details}
				<tr class="detail_row detf_{$report.functions[func].id}">
					<td colspan="2" class="first"></td>
					<td>{$report.functions[func].details[func_detail].query}</td>
					<td>{$report.functions[func].details[func_detail].args}</td>
					<td>{$report.functions[func].details[func_detail].selector}</td>
					<td>{$report.functions[func].details[func_detail].context}</td>
					<td class="numerical">{$report.functions[func].details[func_detail].runtime} ms</td>
					<td class="numerical">{$report.functions[func].details[func_detail].percent}</td>
					<td class="last"></td>
				</tr>
				{/section}
			{/section}
		</tbody>
	</table>
	
	<h3>Queries</h3>
	<table>
		<thead>
			<tr>
				<th class="first">Event</th>
				<th>Function</th>
				<th>Query</th>
				<th>Arguments</th>
				<th>Selector</th>
				<th>Context</th>
				<th>In</th>
				<th>Out</th>
				<th colspan="2">Runtime</th>					
				<th class="last">Details</th>
			</tr>
		</thead>
		
		<tbody>
			{section name=quer loop=$report.queries}
				{if $report.queries[quer].details}
				<tr class="{cycle values="odd, even"} detail_toggle" id="detq_{$report.queries[quer].id}">
				{else}
				<tr class="{cycle values="odd, even"}">
				{/if}
					<td class="first">{$report.queries[quer].event}</td>
					<td>{$report.queries[quer].functionName}</td>
					<td>{$report.queries[quer].query}</td>
					<td>{$report.queries[quer].args}</td>
					<td>{$report.queries[quer].selector}</td>
					<td>{$report.queries[quer].context}</td>
					<td class="numerical">{$report.queries[quer].inLength}</td>
					<td class="numerical">{$report.queries[quer].outLength}</td>
					<td class="numerical">{$report.queries[quer].methodDuration}</td>
					<td class="numerical">{$report.queries[quer].percent}</td>	
					<td class="last">
						{if $report.queries[quer].details}
							<img height="16px" width="16px" class="ui-icon ui-icon-folder-collapsed" />
						{/if}
					</td>
				</tr>
				
				{section name=quer_detail loop=$report.queries[quer].details}
				<tr class="detail_row detq_{$report.queries[quer].id}">
					<td colspan="2" class="first"></td>
					<td>{$report.queries[quer].details[quer_detail].query}</td>
					<td colspan="5">{$report.queries[quer].details[quer_detail].args}</td>
					<td class="numerical">{$report.queries[quer].details[quer_detail].methodDuration}</td>
					<td class="numerical">{$report.queries[quer].details[quer_detail].percent}</td>
					<td></td>
				</tr>
				{/section}					
			{/section}
		</tbody>
	</table>
</div>
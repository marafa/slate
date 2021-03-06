{extends designs/site.tpl}

{block title}Import iCal &gt; Calender Events &mdash; {$dwoo.parent}{/block}

{block content}
	<h1>Import Ical events</h1>
	<h2>Instructions</h2>
	<ul>
		<li><a href="/manage#integration/ical">Input and manage list of linked iCal feeds in the Slate management console</a></li>
		<li>Save and import here. Use pretend mode first to check changes.</li>
	</ul>

	<h2>Input</h2>
    <h3>Run from template</h3>
    <ul>
        {foreach item=TemplateJob from=$templates}
            <li><a href="/integrations/ical/{$TemplateJob->Handle}" title="{$TemplateJob->Config|http_build_query|escape}">Job #{$TemplateJob->ID} &mdash; created by {$TemplateJob->Creator->Username} on {$TemplateJob->Created|date_format:'%c'}</a></li>
        {/foreach}
    </ul>

    <h3>Run new job</h3>
	<form method="POST">
		<p>
			<label>
				Pretend
				<input type="checkbox" name="pretend" value="true" {refill field=pretend checked="true" default="true"}>
			</label>
			(Check to prevent saving any changes to the database)
		</p>
		<p>
			<label>
				Verbose
				<input type="checkbox" name="verbose" value="true" {refill field=verbose checked="true" default="true"}>
			</label>
			(Check to see detailed output about what changes will be/have been made)
		</p>
		<p>
			<label>
				Create Template
				<input type="checkbox" name="createTemplate" value="true" {refill field=createTemplate checked="true"}>
			</label>
			(Check to create a template job that can be repeated automatically instead of running it now)
		</p>
		<p>
			<label>
				Email report
				<input type="text" name="reportTo" {refill field=reportTo} length="100">
			</label>
			Email recipient or list of recipients to send post-sync report to
		</p>

		<p>
			<label>
				Currently Connected Feeds
				<ul>
					<?php
						foreach (Emergence\Events\Feed::getAll() AS $Feed) {
							echo "<li><a href='$Feed->Link'>$Feed->Title</a></li>";
						}
					?>
				</ul>
			</label>
		</p>
		<input type="submit" value="Synchronize">
	</form>
	
	<h2>Output</h2>
	<pre>
<?php;
/*
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		try {
			
			iCalIntegrator::$responseMode = 'return';
			
			// configure mode
			iCalIntegrator::$pretend = !empty($_REQUEST['pretend']);
			if (iCalIntegrator::$pretend) {
				print("<strong>Pretend mode</strong> activated\n");
			}
			
			iCalIntegrator::$verbose = !empty($_REQUEST['verbose']);
			if (iCalIntegrator::$verbose) {
				print("Verbose output activated\n");
			}
			
			// init job
			$Job = SynchronizationJob::create(array(
				'Integrator' => 'iCalIntegrator'
				,'Config' => array()
			));
		
			// execute job
			print "Executing merge...\n\n";
			$response = iCalIntegrator::handleExecuteJobRequest($Job);
			
			// save job results
#			if (!iCalIntegrator::$pretend) {
#				$Job->Status = 'Completed';
#				$Job->Results = $results;
#				$Job->save();
#			}
	
			print "\n\nMerge finished, results:\n";

			print_r($response['data']['data']->Results);
			
		} catch(Exception $e) {
			print "\n\nImport failed, exception caught:\n";
			print_r($e);
		}
	}
*/
?>
	</pre>
{/block}
<?php defined('SYSPATH') OR die('No direct access allowed.');

foreach ($results as $class => $methods)
{
	echo "\n\n" . Kohana::lang('unit_test.class') . ': ' . $class . "\n\n";
	printf('%s: %.2f%%', Kohana::lang('unit_test.score'), $stats[$class]['score']);
	echo ",\n" . Kohana::lang('unit_test.total'),  ': ', $stats[$class]['total'] . ",\n";
	echo Kohana::lang('unit_test.passed'), ': ', $stats[$class]['passed'] . ",\n";
	echo Kohana::lang('unit_test.failed'), ': ', $stats[$class]['failed'] . ",\n";
	echo Kohana::lang('unit_test.errors'), ': ', $stats[$class]['errors'] . "\n\n";
	
	if (empty($methods))
	{
		echo Kohana::lang('unit_test.no_tests_found');
	}
	else
	{
		foreach ($methods as $method => $result)
		{
			// Hide passed tests from report
			if ($result === TRUE AND $hide_passed === TRUE)
				continue;
			
			echo Kohana::lang('unit_test.method') . ': ' . $method . ': ';
			
			if ($result === TRUE)
			{
				echo Kohana::lang('unit_test.passed') . "\n";
			}
			else
			{
				echo Kohana::lang('unit_test.failed') . "\n\t" . $result->getMessage() . "\n";
			}
		}
	}
}
<?php
foreach ($results as $class => $methods) {
  echo str_repeat("-", 100), "\n";
  echo $class, "\n";
  echo str_repeat("-", 100), "\n";

  foreach (array("score", "total", "passed", "failed", "errors") as $key) {
    @$totals[$key] += $stats[$class][$key];
  }

  if (empty($methods)) {
    echo Kohana::lang("unit_test.no_tests_found"), "\n";
  } else {
    foreach ($methods as $method => $result) {
      // Hide passed tests from report
      if ($result === true AND $hide_passed === true) {
	continue;
      }
      printf("%-40.40s", $method);
      if ($result === true) {
	echo Kohana::lang("unit_test.passed"), "\n";
      } else if ($result instanceof Kohana_Unit_Test_Exception) {
	echo Kohana::lang("unit_test.failed"), "\n";
	echo "  ", html::specialchars($result->getMessage()), "\n";
	echo "  ", html::specialchars($result->getFile());
	echo " ", "(" . Kohana::lang("unit_test.line") . " " . $result->getLine(), ")\n";
	if ($result->getDebug() !== null) {
	  echo "  ", "(", gettype($result->getDebug()), ") ",
	    html::specialchars(var_export($result->getDebug(), true)), "\n";
	}
      } else if ($result instanceof Exception) {
	echo Kohana::lang("unit_test.error"), "\n";
	if ($result->getMessage()) {
	  echo "  ", html::specialchars($result->getMessage()), "\n";
	}
	echo "  ", html::specialchars($result->getFile()), " (",
	  Kohana::lang("unit_test.line"), " ", $result->getLine(), ")\n";
      }
    }
  }

  echo str_repeat("=", 100), "\n";
  printf(">> %s\t%s: %.2f%%\t%s: %d\t%s: %d\t%s: %d\t%s: %d\n",
	 $class,
	 Kohana::lang("unit_test.score"), $stats[$class]["score"],
	 Kohana::lang("unit_test.total"), $stats[$class]["total"],
	 Kohana::lang("unit_test.passed"), $stats[$class]["passed"],
	 Kohana::lang("unit_test.failed"), $stats[$class]["failed"],
	 Kohana::lang("unit_test.errors"), $stats[$class]["errors"]);
  echo str_repeat("-", 100), "\n\n\n";
}

printf(">> TOTAL\t%s: %.2f%%\t%s: %d\t%s: %d\t%s: %d\t%s: %d\n",
       Kohana::lang("unit_test.score"), 100 * ($totals["passed"] / $totals["total"]),
       Kohana::lang("unit_test.total"), $totals["total"],
       Kohana::lang("unit_test.passed"), $totals["passed"],
       Kohana::lang("unit_test.failed"), $totals["failed"],
       Kohana::lang("unit_test.errors"), $totals["errors"]);

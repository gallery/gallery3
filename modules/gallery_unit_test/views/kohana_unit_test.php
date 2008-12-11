<? defined("SYSPATH") or die("No direct script access."); ?>
<?php
foreach ($results as $class => $methods) {
  echo "+", str_repeat("-", 98), "+\n";
  printf("| %-96.96s |\n", $class);
  echo "+", str_repeat("-", 57), "+", str_repeat("-", 40), "+\n";

  foreach (array("score", "total", "passed", "failed", "errors") as $key) {
    @$totals[$key] += $stats[$class][$key];
  }

  if (empty($methods)) {
    printf("| %-96.96s |\n", "NO TESTS FOUND");
  } else {
    foreach ($methods as $method => $result) {
      // Hide passed tests from report
      if ($result === true AND $hide_passed === true) {
        continue;
      }
      printf("| %-56.56s", $method);
      if ($result === true) {
        printf("| PASS                                   |\n");
      } else if ($result instanceof Kohana_Unit_Test_Exception) {
        printf("| FAIL                                   |\n");
        echo "  ", $result->getMessage(), "\n";
        echo "  ", $result->getFile();
        echo " ", "(" . Kohana::lang("unit_test.line") . " " . $result->getLine(), ")\n";
        if ($result->getDebug() !== null) {
          echo "  ", "(", gettype($result->getDebug()), ") ",
            var_export($result->getDebug(), true), "\n";
        }
        echo "\n";
      } else if ($result instanceof Exception) {
        printf("| ERROR                                  |\n");
        if ($result->getMessage()) {
          echo "  ", $result->getMessage(), "\n";
        }
        echo "  ", $result->getFile(), " (Line ", $result->getLine(), ")\n";
        echo "\n";
      }
    }
  }

  echo "+", str_repeat("=", 57), "+", str_repeat("=", 40), "+\n";
  printf("| %-40.40s %-13.13s %-13.13s %-13.13s %-13.13s |\n",
         $class,
         "Score: {$stats[$class]['score']}",
         "Total: {$stats[$class]['total']}",
         "PASS: {$stats[$class]['passed']}",
         "FAIL: {$stats[$class]['failed']}",
         "ERROR: {$stats[$class]['errors']}");
  echo "+", str_repeat("=", 98), "+\n\n\n";
}

printf("  %-40.40s %-13.13s %-13.13s %-13.13s %-13.13s\n",
       "TOTAL",
       "Score: " . ($totals["total"] ? 100 * ($totals["passed"] / $totals["total"]) : 0),
       "Total: {$totals['total']}",
       "PASS: {$totals['passed']}",
       "FAIL: {$totals['failed']}",
       "ERROR: {$totals['errors']}");

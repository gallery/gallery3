<? defined("SYSPATH") or die("No direct script access."); ?>
<?php

function green_start() {
  return "\x1B[32m";
}

function color_end() {
  return "\x1B[0m";
}

function red_start() {
  return "\x1B[31m";
}

function magenta_start() {
  return "\x1B[35m";
}

foreach ($results as $class => $methods) {
  echo "+", str_repeat("-", 98), "+\n";
  printf("| %-96.96s |\n", $class);
  echo "+", str_repeat("-", 87), "+", str_repeat("-", 10), "+\n";

  foreach (array("score", "total", "passed", "failed", "errors") as $key) {
    @$totals[$key] += $stats[$class][$key];
  }

  if (empty($methods)) {
    printf("| %s%-96.96s%s |\n", magenta_start(), "NO TESTS FOUND", color_end());
  } else {
    foreach ($methods as $method => $result) {
      // Hide passed tests from report
      if ($result === true AND $hide_passed === true) {
        continue;
      }
      if ($result === true) {
        printf("| %s%-85.85s%s | %sPASS%s     |\n",
               green_start(), $method, color_end(),
               green_start(), color_end());
      } else if ($result instanceof Kohana_Unit_Test_Exception) {
        printf("| %s%-85.85s%s | %sFAIL%s     |\n",
               red_start(), $method, color_end(),
               red_start(), color_end());
        echo "  ", $result->getMessage(), "\n";
        echo "  ", $result->getFile();
        echo " ", "(" . Kohana::lang("unit_test.line") . " " . $result->getLine(), ")\n";
        if ($result->getDebug() !== null) {
          echo "  ", "(", gettype($result->getDebug()), ") ",
            var_export($result->getDebug(), true), "\n";
        }
        echo "\n";
      } else if ($result instanceof Exception) {
        printf("| %s%-85.85s%s | %sERROR%s    |\n",
               magenta_start(), $method, color_end(),
               magenta_start(), color_end());
        if ($result->getMessage()) {
          echo "  ", $result->getMessage(), "\n";
        }
        echo "  ", $result->getFile(), " (Line ", $result->getLine(), ")\n";
        echo "\n";
        echo $result->getTraceAsString(), "\n";
      }
    }
  }

  echo "+", str_repeat("=", 87), "+", str_repeat("=", 10), "+\n";
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

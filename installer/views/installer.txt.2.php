<?php defined("SYSPATH") or die("No direct script access.");
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

echo "+", str_repeat("-", 98), "+\n";
printf("| %-96.96s |\n", "Environment Tests");
printf("| %-96.96s |\n", "The following tests have been run to determine if Gallery3 will work\n");
printf("in your environment. If any of the tests have failed, consult the documention on\n");
printf("http://gallery.menalto.com for more information on how to correct the problem.");
echo "+", str_repeat("-", 98), "+\n";

 
     //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     //   <th>PHP Version</th>                                                                                                                    // //
     //                                                                                                                                           // //
     //   <? if (empty(self::$errors["php_version"])): ?>                                                                                         // //
     //   <td class="pass"><?php echo PHP_VERSION ?></td>                                                                                         // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">Gallery3 requires PHP 5.2 or newer, this version is <?php echo PHP_VERSION ?>.</td>                                    // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //   <th>System Directory</th>                                                                                                               // //
     //   <?php if (empty(self::$errors["syspath"])): ?>                                                                                          // //
     //   <td class="pass"><?php echo SYSPATH ?></td>                                                                                             // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>                   // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //   <th>Application Directory</th>                                                                                                          // //
     //   <?php if (empty(self::$errors["apppath"])): ?>                                                                                          // //
     //   <td class="pass"><?php echo APPPATH ?></td>                                                                                             // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">The configured <code>application</code> directory does not exist or does not contain required files.</td>              // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //   <th>Modules Directory</th>                                                                                                              // //
     //   <?php if (empty(self::$errors["modpath"])): ?>                                                                                          // //
     //   <td class="pass"><?php echo MODPATH ?></td>                                                                                             // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">The configured <code>modules</code> directory does not exist or does not contain required files.</td>                  // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //   <th>PCRE UTF-8</th>                                                                                                                     // //
     //   <?php if (!empty(self::$errors["utf-8"])): ?>                                                                                           // //
     //   <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>                                  // //
     //   <?php elseif (!empty(self::$errors["unicode"])): ?>                                                                                     // //
     //   <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>                       // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="pass">Pass</td>                                                                                                              // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //   <th>Reflection Enabled</th>                                                                                                             // //
     //   <?php if (empty(self::$errors["reflection"])): ?>                                                                                       // //
     //   <td class="pass">Pass</td>                                                                                                              // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">PHP <a href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>               // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //   <th>Filters Enabled</th>                                                                                                                // //
     //   <?php if (empty(self::$errors["filter_list"])): ?>                                                                                      // //
     //   <td class="pass">Pass</td>                                                                                                              // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">The <a href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>             // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //   <th>Iconv Extension Loaded</th>                                                                                                         // //
     //   <?php if (empty(self::$errors["iconv"])): ?>                                                                                            // //
     //   <td class="pass">Pass</td>                                                                                                              // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>                                             // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     //                                                                                                                                           // //
     // <tr>                                                                                                                                      // //
     //   <th>Mbstring Not Overloaded</th>                                                                                                        // //
     //   <?php if (empty(self::$errors["mbstring"])): ?>                                                                                         // //
     //   <td class="pass">Pass</td>                                                                                                              // //
     //   <?php else: ?>                                                                                                                          // //
     //   <td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>        // //
     //   <?php endif ?>                                                                                                                          // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //    <th>URI Determination</th>                                                                                                             // //
     //    <?php if (empty(self::$errors["uri"])): ?>                                                                                             // //
     //    <td class="pass">Pass</td>                                                                                                             // //
     //    <?php else: ?>                                                                                                                         // //
     //    <td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code> or <code>$_SERVER['PHP_SELF']</code> is available.</td>                  // //
     //    <?php endif ?>                                                                                                                         // //
     // </tr>                                                                                                                                     // //
     // <tr>                                                                                                                                      // //
     //    <th>PHP Short Tags</th>                                                                                                                // //
     //    <?php if (empty(self::$errors["short tags"])): ?>                                                                                      // //
     //    <td class="pass">Pass</td>                                                                                                             // //
     //    <?php else: ?>                                                                                                                         // //
     //    <td class="fail">Gallery3 needs php short tags enabled.</td>                                                                           // //
     //    <?php endif ?>                                                                                                                         // //
     // </tr>                                                                                                                                     // //
     //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 

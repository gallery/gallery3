<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Kohana benchmarks are prefixed to prevent collisions
define('SYSTEM_BENCHMARK', 'system_benchmark');

// Load benchmarking support
require SYSPATH.'core/Benchmark'.EXT;

// Start total_execution
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution');

// Start kohana_loading
Benchmark::start(SYSTEM_BENCHMARK.'_kohana_loading');

// Load core files
require SYSPATH.'core/Event'.EXT;
final class Event extends Event_Core {}

require SYSPATH.'core/Kohana'.EXT;
final class Kohana extends Kohana_Core {}

require SYSPATH.'core/Kohana_Exception'.EXT;
require MODPATH.'gallery/libraries/MY_Kohana_Exception'.EXT;

require SYSPATH.'core/Kohana_Config'.EXT;
require SYSPATH.'libraries/drivers/Config'.EXT;
require SYSPATH.'libraries/drivers/Config/Array'.EXT;
final class Kohana_Config extends Kohana_Config_Core {}

// Prepare the environment
Kohana::setup();

// End kohana_loading
Benchmark::stop(SYSTEM_BENCHMARK.'_kohana_loading');

// Start system_initialization
Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');

// Prepare the system
Event::run('system.ready');

// Determine routing
Event::run('system.routing');

// End system_initialization
Benchmark::stop(SYSTEM_BENCHMARK.'_system_initialization');

// Make the magic happen!
Event::run('system.execute');
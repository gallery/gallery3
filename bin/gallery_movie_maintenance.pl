#!/usr/bin/perl -w

use strict;
use File::Basename;
use Data::Dumper;

$| = 1;

# 0 = no output except for the ffmpeg output
# 1 = in addition to the above, some info about which files are being processd
# 2 = in addition to the above, lots more info about which files are being scanned and processed
my $DEBUG = 2;
my $ALWAYS_CREATE_THUMB = 0;

# path to your var directory with the photos
# e.g. /home/bdutton/public_html/gallery3/var
my $VAR_DIR = '/var/www/html/var';

my $VIDEO_BITRATE = '3000k'; # ffmpeg video bit rate argument
# audio codec to use for ffmpeg, I use libfdk_aac on freebsd. My CentoOS test site
# wants -strict -2 added because aac is apparently experimental
my $ACODEC = 'aac -strict -2';

my $CHOWN_USER = ''; # set this if you want chown to change the ownership of the generated files

my $CLI_SUDO = ''; # set this to the sudo executable if you want to run the conversion as a different user
my $CLI_FIND = 'find'; # path to find, maybe I should use a perl module for this?
my $CLI_CHOWN = 'chown'; # path to chown
my $CLI_FFMPEG = 'ffmpeg'; # path to ffmpeg

# video extensions you want to process
my @SUFFIXES = (
    'mp4',
    'm4v',
    '3gp',
    'avi',
    'mpg',
    'mpeg',
    'mov',
    'asf',
    'wmv',
    'flv',
    'mts',
);

#
# need more error checking for above settings
#

# should be the var folder of the gallery installation
my $dir1 = $ARGV[0] || $VAR_DIR;

foreach my $dir ($dir1) {
    if (!-d $dir) {
        print "$dir is not a directory\n";
        exit;
    }
}

my @lower = @SUFFIXES;
foreach (@lower) {
	push(@SUFFIXES, uc($_));
}

my $results = {};

foreach my $dir ($dir1) {
	#
	# need quoting here
	#
	chdir($dir);
	my @files = `$CLI_FIND . -type f`;

	foreach my $file (@files) {
		chomp($file);

		my ($name,$path,$ext) = fileparse($file, @SUFFIXES);

		next unless ($ext);

		$path =~ m#^\./(albums|thumbs|resizes)/(.*)$#;

		$results->{$1}{$2}{$name} = $ext;
	}
}

#print Dumper($results);

#print "\nDONE\n\n";

# create flv files
foreach my $path (keys %{$results->{albums}}) {
	#print "check $path\n";

	my $h = $results->{albums}{$path};

	foreach my $fname (keys %$h) {
		my $ext = $h->{$fname};

		print "check $path$fname\n" if ($DEBUG);

		#next if (lc($ext) eq 'mp4');

		my $create_thumb = 0;
		if ($results->{resizes}{$path}{$fname.$ext.'.'}) {
			my $thumb_ext = $results->{resizes}{$path}{$fname.$ext.'.'};

			if ($thumb_ext eq 'mp4') {
				print "albums/$path$fname has mp4\n" if ($DEBUG >= 2);
			} else {
				$create_thumb = 1;
			}
		} else {
			$create_thumb = 1;
		}

		$create_thumb = 1 if ($ALWAYS_CREATE_THUMB);

		if ($create_thumb) {
			my $pixfmt = lc($ext) eq 'avi' ? '-pix_fmt yuv420p' : '';

			my @cmds = (
				qq{$CLI_SUDO $CLI_FFMPEG -i 'albums/$path$fname$ext' -y -c:v libx264 -profile:v main $pixfmt -level 4.0 -preset slow -b:v $VIDEO_BITRATE -acodec $ACODEC -ac 2 -ar 44100 -b:a 64k 'resizes/$path$fname$ext.mp4'},
			);

			if ($CHOWN_USER) {
				push(@cmds, "$CLI_SUDO $CLI_CHOWN $CHOWN_USER 'resizes/$path$fname$ext.mp4'");
			}

			foreach (@cmds) {
				print if ($DEBUG);
				`$_`;
			}
		}
	}
}

foreach my $path (keys %{$results->{resizes}}) {
    my $h = $results->{resizes}{$path};

    foreach my $fname (keys %$h) {
	    my $ext = $h->{$fname};

	    # chomp off ending .
	    $fname =~ s/\..+?\.$/./;
	    if ($results->{albums}{$path}{$fname}) {
		    print "resizes/$path$fname has counterpart\n" if ($DEBUG >= 2);
	    } else {
		    print "resizes/$path$fname is orphaned?\n" if ($DEBUG);
	    }
    }
}

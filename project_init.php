<?php
// This file is part of BOINC.
// http://boinc.berkeley.edu
// Copyright (C) 2017 University of California
//
// BOINC is free software; you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation,
// either version 3 of the License, or (at your option) any later version.
//
// BOINC is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with BOINC.  If not, see <http://www.gnu.org/licenses/>.

// initialize or add to the project DB
// Use this to add projects.
// To edit existing projects, use the web interface

require_once("../inc/keywords.inc");
require_once("../inc/su_db.inc");

function make_project($name, $url, $keywords, $web_rpc_url_base=null) {
    global $job_keywords;

    $project = SUProject::lookup("url='$url'");
    if ($project) {
        echo "project $name already in DB\n";
        return;
    }

    $now = time();
    $cmd = "~/boinc/lib/crypt_prog -sign_string $url ~/science_united/code_sign_private";
    $out = array();
    $retval = 0;
    exec($cmd, $out, $retval);
    if ($retval) {
        die("$cmd failed\n");
    }
    if (!$web_rpc_url_base) {
        $web_rpc_url_base = $url;
    }
    $url_signature = implode("\n", $out);
    $id = SUProject::insert("(create_time, name, url, web_rpc_url_base, url_signature, allocation) values ($now, '$name', '$url', '$web_rpc_url_base', '$url_signature', 10)");

    foreach ($keywords as $k) {
        $kw_id = $k[0];
        $frac = $k[1];
        while (true) {
            // insert all ancestors too
            //
            SUProjectKeyword::insert("(project_id, keyword_id, work_fraction) values ($id, $kw_id, $frac)");
            $kw = $job_keywords[$kw_id];
            if ($kw->level > 0) {
                $kw_id = $kw->parent;
            } else {
                break;
            }
        }
    }

    echo "Added project $name\n";
}

function make_projects() {
    make_project("LHC@home",
        "https://lhcathome.cern.ch/lhcathome/",
        array(
            array(KW_PARTICLE_PHYSICS, 1),
            array(KW_CERN, 1),
        )
    );
    make_project("SETI@home",
        "http://setiathome.berkeley.edu/",
        array(
            array(KW_SETI, 1),
            array(KW_UCB, 1),
        ),
        "https://setiathome.berkeley.edu/"
    );
    make_project("Rosetta@home",
        "http://boinc.bakerlab.org/rosetta/",
        array(
            array(KW_PROTEINS, 1),
            array(KW_UW, 1),
        )
    );
    make_project("BOINC Test Project",
        "http://boinc.berkeley.edu/test/",
        array(
            array(KW_MATH_CS, 1),
            array(KW_UCB, 1),
        )
    );
if (0) {
    // WCG is problematic because it doesn't use email addr for user ID
    make_project("World Community Grid",
        "http://www.worldcommunitygrid.org/",
        array(
            array(KW_BIOMED, .5),
            array(KW_EARTH_SCI, .7),
            array(KW_US, .5),
        ),
        "https://www.worldcommunitygrid.org/"
    );
}
    make_project("Herd",
        "http://herd.tacc.utexas.edu/",
        array(
            array(KW_BIOMED, .5),
            array(KW_PHYSICS, .5),
            array(KW_US, 1),
        )
    );
    make_project("nanoHUB",
        "https://boinc.nanohub.org",
        array(
            array(KW_NANOSCIENCE, 1),
            array(KW_US, 1),
        )
    );
}

make_projects();

?>
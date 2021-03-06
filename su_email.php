#! /usr/bin/env php
<?php
// This file is part of BOINC.
// http://boinc.berkeley.edu
// Copyright (C) 2018 University of California
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

require_once("../inc/boinc_db.inc");
require_once("../inc/email.inc");
require_once("../inc/su.inc");

// send status emails
//

function log_write($x) {
    echo date(DATE_RFC822), ": $x\n";
    flush();
}

function su_send_email($user, $x) {
    $x = '<html><body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:12px; color:#666666;\">'.$x;
    $x .= "<p>Thanks for participating in Science United.<p><p>";
    $unsubscribe_url = opt_out_url($user, "su_unsubscribe.php");
    $x .= "<small><a href=$unsubscribe_url>Unsubscribe</a> or ";
    $x .= sprintf(
        "<a href=%s>change the frequency of these emails</a>.",
        "https://scienceunited.org/su_email_prefs.php"
    );
    $x .= "</small></body></html>\n";
    send_email($user, "Science United status", null, $x);
    log_write("sent email to $user->email_addr");
}

function do_user($user) {
    $x = "Dear $user->name:<p><p>Greetings from Science United. ";

    $hosts = BoincHost::enum("userid = $user->id and total_credit>=0");
    if (count($hosts) == 0) {
        $x .= "We haven't heard from your computer yet.  Please <a href=https://scienceunited.org/su_help.php>make sure that BOINC is installed and running<a>.";
        su_send_email($user, $x);
        return;
    }

    // $ndays is the user's requested email frequency.
    // report progress in that period
    //
    $ndays = $user->send_email;

    // add 1 day because of accounting period
    //
    $t0 = time() - ($ndays+1)*86400;
    $uas = SUAccountingUser::enum("user_id = $user->id and create_time > $t0");
    $delta = new_delta_set();
    foreach ($uas as $ua) {
        add_record($ua, $delta);
    }

    if ($ndays == 1) {
        $ndays_str = "day";
    } else {
        $ndays_str = "$ndays days";
    }

    if (delta_set_nonzero($delta)) {
        $x .= sprintf(
            'In the last %s your computers have contributed %s hours of processing time, and have completed %d jobs.  Congratulations!',
            $ndays_str,
            show_num(($delta->cpu_time+$delta->gpu_time)/3600.),
            $delta->njobs_success+$delta->njobs_fail
        );
        $x .= " For details, <a href=https://scienceunited.org/>visit Science United</a>.";
        $x .= "<p><p>\n";

        $t0 = time() - 86400.*7;
        foreach ($hosts as $host) {
            $idle_days = (time() - $host->rpc_time)/86400;
            if ($host->rpc_time < $t0) {
                $x .= sprintf(
                    "Your computer '%s' hasn't contacted us in %d days; please check that BOINC is running there.<p>",
                    $host->domain_name, (int)$idle_days
                );
            }
        }
    } else {
        $x .= sprintf(
            "Your computers haven't reported work in the last %s.
            Please check that BOINC is installed, unsuspended,
            and attached to Science United.",
            $ndays_str
        );
        $x .= " Get help <a href=https://scienceunited.org/su_help.php>here</a>.";
        $x .= "<p><p>\n";
    }
    su_send_email($user, $x);
}

function main() {
    log_write("starting");
    $now = time();
    $users = BoincUser::enum("send_email > 0 and seti_last_result_time < $now");
    foreach ($users as $user) {
        do_user($user);
    }
    log_write("done");
}

//do_user(BoincUser::lookup_id(22203));

main();

?>

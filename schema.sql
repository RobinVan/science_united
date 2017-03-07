create table su_keyword (
    id                      integer         not null auto_increment,
    word                    varchar(254)    not null,
    category                tinyint         not null,
    primary key (id)
) engine=InnoDB;

create table su_project (
    id                      integer         not null auto_increment,
    create_time             double          not null,
    name                    varchar(254)    not null,
    url                     varchar(254)    not null,
    url_signature           varchar(1024)   not null,
    allocation              double          not null,
    credit                  double          not null,
    primary key (id)
) engine=InnoDB;

create table su_project_keyword (
    project_id              integer         not null,
    keyword_id              integer         not null,
    unique(project_id, keyword_id)
) engine=InnoDB;

create table su_user_keyword (
    user_id                 integer         not null,
    keyword_id              integer         not null,
    type                    smallint        not null,
    unique(user_id, keyword_id)
) engine=InnoDB;

/*
 * a user account on a project, possibly failed or in-progress
 */
create table su_account (
    user_id                 integer         not null,
    project_id              integer         not null,
    authenticator           varchar(254)    not null,
    state                   smallint        not null,
    retry_time              double          not null,
    cpu_ec                  double          not null,
    cpu_time                double          not null,
    gpu_ec                  double          not null,
    gpu_time                double          not null,
    njobs_success           integer         not null,
    njobs_fail              integer         not null,
    unique(user_id, project_id),
    index account_state (state)
) engine=InnoDB;

/*
 * Per (host, project) record. No history.
 * Stores totals at last AM RPC;
 * used to compute deltas.
 */
create table su_host_project (
    host_id                 integer         not null,
    project_id              integer         not null,
    last_rpc                double          not null,
    cpu_ec                  double          not null,
    cpu_time                double          not null,
    gpu_ec                  double          not null,
    gpu_time                double          not null,
    njobs_success           integer         not null,
    njobs_fail              integer         not null,
    unique(host_id, project_id)
) engine=InnoDB;

/*
 * historical accounting records, for
 * - total
 * - per project
 * - per user
 *
 * We could also have per-host, per-user-project, per-host-project etc.
 * but not worth it.
 */

create table su_accounting (
    id                      integer         not null auto_increment,
    create_time             double          not null,
    cpu_ec_delta            double          not null,
    cpu_ec_total            double          not null,
    gpu_ec_delta            double          not null,
    gpu_ec_total            double          not null,
    cpu_time_delta          double          not null,
    cpu_time_total          double          not null,
    gpu_time_delta          double          not null,
    gpu_time_total          double          not null,
    nactive_hosts           integer         not null,
    nactive_hosts_gpu       integer         not null,
    nactive_users           integer         not null,
    njobs_success_delta     integer         not null,
    njobs_success_total     integer         not null,
    njobs_fail_delta        integer         not null,
    njobs_fail_total        integer         not null,
    primary key (id)
) engine=InnoDB;


create table su_accounting_project (
    id                      integer         not null auto_increment,
    create_time             double          not null,
    project_id              integer         not null,
    cpu_ec_delta            double          not null,
    cpu_ec_total            double          not null,
    gpu_ec_delta            double          not null,
    gpu_ec_total            double          not null,
    cpu_time_delta          double          not null,
    cpu_time_total          double          not null,
    gpu_time_delta          double          not null,
    gpu_time_total          double          not null,
    njobs_success_delta     integer         not null,
    njobs_success_total     integer         not null,
    njobs_fail_delta        integer         not null,
    njobs_fail_total        integer         not null,
    index (project_id),
    primary key (id)
) engine=InnoDB;

create table su_accounting_user (
    id                      integer         not null auto_increment,
    create_time             double          not null,
    user_id                 integer         not null,
    cpu_ec_delta            double          not null,
    cpu_ec_total            double          not null,
    gpu_ec_delta            double          not null,
    gpu_ec_total            double          not null,
    cpu_time_delta          double          not null,
    cpu_time_total          double          not null,
    gpu_time_delta          double          not null,
    gpu_time_total          double          not null,
    njobs_success_delta     integer         not null,
    njobs_success_total     integer         not null,
    njobs_fail_delta        integer         not null,
    njobs_fail_total        integer         not null,
    index (user_id),
    primary key (id)
) engine=InnoDB;

-- DDL domain Hijauku, diringkas dari database/migrations.
-- Hanya definisi tabel, kolom, indeks, dan relasi. Tidak ada INSERT atau data seed.
-- Tabel M16-M18 disertakan karena masih ada pada migration, tetapi di luar scope aktif.

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(24) NOT NULL DEFAULT 'citizen',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB;

CREATE TABLE programs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    created_by BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    objective TEXT NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY programs_status_starts_on_index (status, starts_on),
    CONSTRAINT programs_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE sites (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    program_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY sites_program_id_name_index (program_id, name),
    CONSTRAINT sites_program_id_foreign FOREIGN KEY (program_id) REFERENCES programs (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE plots (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    capacity SMALLINT UNSIGNED NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY plots_site_id_code_unique (site_id, code),
    CONSTRAINT plots_site_id_foreign FOREIGN KEY (site_id) REFERENCES sites (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE tree_species (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    care_notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY tree_species_name_unique (name),
    KEY tree_species_is_active_name_index (is_active, name)
) ENGINE=InnoDB;

CREATE TABLE planting_batches (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    plot_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    planted_on DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY planting_batches_plot_id_code_unique (plot_id, code),
    UNIQUE KEY planting_batches_id_plot_id_unique (id, plot_id),
    CONSTRAINT planting_batches_plot_id_foreign FOREIGN KEY (plot_id) REFERENCES plots (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE trees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    species_id BIGINT UNSIGNED NOT NULL,
    plot_id BIGINT UNSIGNED NOT NULL,
    batch_id BIGINT UNSIGNED NULL,
    code VARCHAR(60) NOT NULL,
    planted_on DATE NULL,
    initial_condition VARCHAR(32) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY trees_code_unique (code),
    KEY trees_plot_id_initial_condition_index (plot_id, initial_condition),
    CONSTRAINT trees_species_id_foreign FOREIGN KEY (species_id) REFERENCES tree_species (id) ON DELETE RESTRICT,
    CONSTRAINT trees_plot_id_foreign FOREIGN KEY (plot_id) REFERENCES plots (id) ON DELETE RESTRICT,
    CONSTRAINT trees_batch_id_plot_id_foreign FOREIGN KEY (batch_id, plot_id) REFERENCES planting_batches (id, plot_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE activities (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    kind VARCHAR(32) NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    quota SMALLINT UNSIGNED NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY activities_site_id_starts_at_index (site_id, starts_at),
    KEY activities_status_starts_at_index (status, starts_at),
    CONSTRAINT activities_site_id_foreign FOREIGN KEY (site_id) REFERENCES sites (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE activity_registrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'registered',
    attended_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY activity_registrations_activity_id_user_id_unique (activity_id, user_id),
    KEY activity_registrations_user_id_status_index (user_id, status),
    CONSTRAINT activity_registrations_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE RESTRICT,
    CONSTRAINT activity_registrations_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE field_assignments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id BIGINT UNSIGNED NOT NULL,
    tree_id BIGINT UNSIGNED NOT NULL,
    assignee_id BIGINT UNSIGNED NOT NULL,
    due_at DATETIME NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'assigned',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY field_assignments_assignee_id_status_due_at_index (assignee_id, status, due_at),
    KEY field_assignments_activity_id_tree_id_index (activity_id, tree_id),
    CONSTRAINT field_assignments_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE RESTRICT,
    CONSTRAINT field_assignments_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT field_assignments_assignee_id_foreign FOREIGN KEY (assignee_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE adoption_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tree_id BIGINT UNSIGNED NOT NULL,
    requester_id BIGINT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    reviewer_id BIGINT UNSIGNED NULL,
    decision_reason TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY adoption_requests_tree_id_status_index (tree_id, status),
    KEY adoption_requests_requester_id_status_index (requester_id, status),
    CONSTRAINT adoption_requests_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT adoption_requests_requester_id_foreign FOREIGN KEY (requester_id) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT adoption_requests_reviewer_id_foreign FOREIGN KEY (reviewer_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE adoption_agreements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_id BIGINT UNSIGNED NOT NULL,
    tree_id BIGINT UNSIGNED NOT NULL,
    adopter_id BIGINT UNSIGNED NOT NULL,
    terms TEXT NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'awaiting_acceptance',
    accepted_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY adoption_agreements_request_id_unique (request_id),
    KEY adoption_agreements_adopter_id_status_index (adopter_id, status),
    KEY adoption_agreements_tree_id_status_index (tree_id, status),
    CONSTRAINT adoption_agreements_request_id_foreign FOREIGN KEY (request_id) REFERENCES adoption_requests (id) ON DELETE RESTRICT,
    CONSTRAINT adoption_agreements_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT adoption_agreements_adopter_id_foreign FOREIGN KEY (adopter_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE tree_adoption_allocations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tree_id BIGINT UNSIGNED NOT NULL,
    request_id BIGINT UNSIGNED NOT NULL,
    agreement_id BIGINT UNSIGNED NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'awaiting_acceptance',
    reserved_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY tree_adoption_allocations_tree_id_unique (tree_id),
    UNIQUE KEY tree_adoption_allocations_request_id_unique (request_id),
    UNIQUE KEY tree_adoption_allocations_agreement_id_unique (agreement_id),
    CONSTRAINT tree_adoption_allocations_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT tree_adoption_allocations_request_id_foreign FOREIGN KEY (request_id) REFERENCES adoption_requests (id) ON DELETE RESTRICT,
    CONSTRAINT tree_adoption_allocations_agreement_id_foreign FOREIGN KEY (agreement_id) REFERENCES adoption_agreements (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE tree_issues (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tree_id BIGINT UNSIGNED NOT NULL,
    reporter_id BIGINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    severity VARCHAR(24) NOT NULL DEFAULT 'normal',
    evidence_path VARCHAR(255) NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    resolution TEXT NULL,
    reviewer_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY tree_issues_status_severity_index (status, severity),
    KEY tree_issues_tree_id_status_index (tree_id, status),
    CONSTRAINT tree_issues_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT tree_issues_reporter_id_foreign FOREIGN KEY (reporter_id) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT tree_issues_reviewer_id_foreign FOREIGN KEY (reviewer_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE care_plans (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tree_id BIGINT UNSIGNED NOT NULL,
    assignment_id BIGINT UNSIGNED NULL,
    planned_on DATE NOT NULL,
    action TEXT NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY care_plans_status_planned_on_index (status, planned_on),
    KEY care_plans_tree_id_planned_on_index (tree_id, planned_on),
    CONSTRAINT care_plans_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT care_plans_assignment_id_foreign FOREIGN KEY (assignment_id) REFERENCES field_assignments (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE care_records (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tree_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NULL,
    performed_by BIGINT UNSIGNED NOT NULL,
    performed_on DATE NOT NULL,
    action TEXT NOT NULL,
    photo_path VARCHAR(255) NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    review_note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY care_records_tree_id_performed_on_index (tree_id, performed_on),
    KEY care_records_status_performed_on_index (status, performed_on),
    CONSTRAINT care_records_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT care_records_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES care_plans (id) ON DELETE RESTRICT,
    CONSTRAINT care_records_performed_by_foreign FOREIGN KEY (performed_by) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT care_records_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE growth_observations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tree_id BIGINT UNSIGNED NOT NULL,
    observed_by BIGINT UNSIGNED NOT NULL,
    observed_on DATE NOT NULL,
    height_cm DECIMAL(8,2) NULL,
    diameter_cm DECIMAL(8,2) NULL,
    condition VARCHAR(32) NOT NULL,
    photo_path VARCHAR(255) NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    review_note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY growth_observations_tree_id_observed_on_unique (tree_id, observed_on),
    KEY growth_observations_status_observed_on_index (status, observed_on),
    CONSTRAINT growth_observations_tree_id_foreign FOREIGN KEY (tree_id) REFERENCES trees (id) ON DELETE RESTRICT,
    CONSTRAINT growth_observations_observed_by_foreign FOREIGN KEY (observed_by) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT growth_observations_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE environment_articles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    author_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY environment_articles_status_published_at_index (status, published_at),
    CONSTRAINT environment_articles_author_id_foreign FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE gallery_entries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id BIGINT UNSIGNED NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    caption TEXT NULL,
    image_path VARCHAR(255) NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY gallery_entries_status_created_at_index (status, created_at),
    CONSTRAINT gallery_entries_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE SET NULL,
    CONSTRAINT gallery_entries_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE green_action_proposals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    proposer_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    site_description TEXT NOT NULL,
    proposed_on DATE NULL,
    participants_estimate SMALLINT UNSIGNED NULL,
    description LONGTEXT NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'draft',
    reviewer_id BIGINT UNSIGNED NULL,
    decision_reason TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY green_action_proposals_status_created_at_index (status, created_at),
    KEY green_action_proposals_proposer_id_status_index (proposer_id, status),
    CONSTRAINT green_action_proposals_proposer_id_foreign FOREIGN KEY (proposer_id) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT green_action_proposals_reviewer_id_foreign FOREIGN KEY (reviewer_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_id BIGINT UNSIGNED NULL,
    action VARCHAR(60) NOT NULL,
    subject_type VARCHAR(255) NULL,
    subject_id BIGINT UNSIGNED NULL,
    from_status VARCHAR(24) NULL,
    to_status VARCHAR(24) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY activity_logs_subject_type_subject_id_index (subject_type, subject_id),
    KEY activity_logs_actor_id_created_at_index (actor_id, created_at),
    CONSTRAINT activity_logs_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

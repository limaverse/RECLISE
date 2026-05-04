-- ============================================================
-- RecLise — ELISSA Support Platform (MESRS)
-- Full Database Schema + Seed Data
-- File: sqlpfe.sql
-- Generated for: login, admin, support, and user pages
-- ============================================================

-- ============================================================
-- 1. DATABASE CREATION
-- ============================================================
CREATE DATABASE IF NOT EXISTS reclise_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE reclise_db;


-- ============================================================
-- 2. TABLES
-- ============================================================

-- ------------------------------------------------------------
-- 2.1 USERS
-- Stores all platform users (admin, support, end-user).
-- Referenced by: requests, messages, audit_logs, etc.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(255)  NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL DEFAULT '',
  full_name     VARCHAR(255)  NOT NULL,
  role          ENUM('user', 'support', 'admin') NOT NULL DEFAULT 'user',
  phone         VARCHAR(50)   DEFAULT NULL,
  department    VARCHAR(100)  DEFAULT NULL,
  status        ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  avatar        VARCHAR(10)   DEFAULT NULL,
  joined_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.2 REQUESTS (Demandes / Réclamations)
-- Central ticket table used across user, support, and admin.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS requests (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  user_id           INT           NOT NULL,
  type              ENUM('request', 'complaint') NOT NULL DEFAULT 'request',
  title             VARCHAR(255)  NOT NULL,
  description       TEXT          NOT NULL,
  category          ENUM('technical', 'access', 'training') NOT NULL DEFAULT 'technical',
  priority          ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
  status            ENUM('new', 'in_progress', 'resolved', 'escalated', 'closed') NOT NULL DEFAULT 'new',
  assigned_to       INT           DEFAULT NULL,
  escalated_to_admin TINYINT(1)   NOT NULL DEFAULT 0,
  support_response  TEXT          DEFAULT NULL,
  closure_motive    VARCHAR(255)  DEFAULT NULL,
  created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_requests_user      FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_requests_assigned  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.3 REQUEST ATTACHMENTS
-- Files attached to a request (1-to-many).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS request_attachments (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  request_id  INT           NOT NULL,
  file_name   VARCHAR(255)  NOT NULL,
  file_path   VARCHAR(500)  DEFAULT NULL,
  uploaded_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_attach_request FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.4 MESSAGES (Conversation thread inside a request)
-- Each message belongs to a request and has a sender type.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  request_id  INT           NOT NULL,
  sender_type ENUM('user', 'support', 'admin', 'system') NOT NULL,
  sender_id   INT           DEFAULT NULL,
  body        TEXT          NOT NULL,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_msg_request FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_sender  FOREIGN KEY (sender_id)  REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.5 AUDIT LOGS
-- Tracks every important action in the system (admin page).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  action      VARCHAR(100)  NOT NULL,
  user_id     INT           DEFAULT NULL,
  details     TEXT          DEFAULT NULL,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.6 TRAINING SESSIONS
-- Support-created training sessions for ELISSA users.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS training_sessions (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(255)  NOT NULL,
  description   TEXT          DEFAULT NULL,
  date          DATE          NOT NULL,
  duration      INT           DEFAULT 1,
  created_by    INT           DEFAULT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_training_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.6b TRAINING REGISTRATIONS
-- Connects users to training sessions.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS training_registrations (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT      NOT NULL,
  session_id    INT      NOT NULL,
  status        ENUM('registered', 'cancelled', 'attended') NOT NULL DEFAULT 'registered',
  registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_trainreg_user    FOREIGN KEY (user_id)    REFERENCES users(id)              ON DELETE CASCADE,
  CONSTRAINT fk_trainreg_session FOREIGN KEY (session_id) REFERENCES training_sessions(id)  ON DELETE CASCADE,
  UNIQUE KEY uq_user_session (user_id, session_id)
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.7 GUIDES & COORDINATORS
-- Support team members for assist/training.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS guides_coordinators (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(255)  NOT NULL,
  email       VARCHAR(255)  NOT NULL,
  type        ENUM('guide', 'coordinator') NOT NULL DEFAULT 'guide',
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.7b ASSIST GUIDES (Knowledge Base)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS assist_guides (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(255)  NOT NULL,
  content     TEXT          NOT NULL,
  category    VARCHAR(50)   DEFAULT 'technical',
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.8 CORRESPONDENCES
-- Types of official correspondence managed by the platform.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS correspondences (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(255)  NOT NULL,
  type          VARCHAR(100)   DEFAULT NULL,
  content       TEXT          DEFAULT NULL,
  template      TEXT          DEFAULT NULL,
  assignee      INT           DEFAULT NULL,
  created_by    INT           DEFAULT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_corr_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_corr_assignee FOREIGN KEY (assignee) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.9 REFERENTIALS
-- Predefined values for sub-tasks, closure motives, etc.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS referentials (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  type  VARCHAR(50)   NOT NULL,
  value VARCHAR(255)   NOT NULL,
  UNIQUE KEY uq_type_value (type, value)
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.10 REFERENTIALS — CLOSURE MOTIVES
-- Predefined reasons for closing / resolving a request.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ref_closure_motives (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.11 DISTRIBUTION BOXES (Casiers de distribution)
-- Named mailboxes that group members together.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS distribution_boxes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(255)  NOT NULL,
  location      VARCHAR(255)  DEFAULT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS distribution_box_members (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  box_id        INT           NOT NULL,
  member_name   VARCHAR(255)  NOT NULL,
  CONSTRAINT fk_box_member FOREIGN KEY (box_id) REFERENCES distribution_boxes(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.12 DISTRIBUTION BOX MEMBERS
-- Many-to-many: links users to distribution boxes.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS distribution_box_members (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  box_id  INT          NOT NULL,
  user_id INT          DEFAULT NULL,
  member_name VARCHAR(255) DEFAULT NULL,

  CONSTRAINT fk_boxmem_box  FOREIGN KEY (box_id)  REFERENCES distribution_boxes(id) ON DELETE CASCADE,
  CONSTRAINT fk_boxmem_user FOREIGN KEY (user_id) REFERENCES users(id)              ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.13 TECHNICAL ISSUES
-- Platform-level technical problems tracked by admin.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS technical_issues (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(255)  NOT NULL,
  description TEXT          NOT NULL,
  priority    ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
  status      ENUM('open', 'resolved') NOT NULL DEFAULT 'open',
  created_by  INT           DEFAULT NULL,
  resolved_at DATETIME      DEFAULT NULL,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_issue_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.14 SYSTEM SETTINGS
-- Key-value store for platform configuration.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS system_settings (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  setting_key    VARCHAR(100) NOT NULL UNIQUE,
  setting_value  VARCHAR(500) DEFAULT NULL,
  updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.15 USER REGISTRATIONS
-- Pending user registration requests (support page).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_registrations (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(255) NOT NULL,
  email       VARCHAR(255) NOT NULL,
  phone       VARCHAR(50)  DEFAULT NULL,
  department  VARCHAR(100) DEFAULT NULL,
  role        ENUM('user', 'support', 'admin') NOT NULL DEFAULT 'user',
  status      ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at DATETIME     DEFAULT NULL,
  reviewed_by INT          DEFAULT NULL,

  CONSTRAINT fk_reg_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.16 USER DASHBOARD CUSTOMIZATIONS
-- Per-user dashboard widget preferences.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_customizations (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  user_id           INT          NOT NULL UNIQUE,
  widget_requests   TINYINT(1)   NOT NULL DEFAULT 1,
  widget_users      TINYINT(1)   NOT NULL DEFAULT 1,
  widget_stats      TINYINT(1)   NOT NULL DEFAULT 1,
  widget_activity   TINYINT(1)   NOT NULL DEFAULT 1,
  workflow          TEXT         DEFAULT NULL,

  CONSTRAINT fk_custom_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2.17 NOTIFICATIONS
-- In-app notifications for users.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT          NOT NULL,
  title       VARCHAR(255) NOT NULL,
  body        TEXT         DEFAULT NULL,
  is_read     TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 3. INDEXES FOR PERFORMANCE
-- ============================================================
CREATE INDEX idx_requests_user_id    ON requests(user_id);
CREATE INDEX idx_requests_status     ON requests(status);
CREATE INDEX idx_requests_priority   ON requests(priority);
CREATE INDEX idx_requests_category   ON requests(category);
CREATE INDEX idx_requests_assigned   ON requests(assigned_to);
CREATE INDEX idx_requests_created    ON requests(created_at);
CREATE INDEX idx_messages_request    ON messages(request_id);
CREATE INDEX idx_messages_created    ON messages(created_at);
CREATE INDEX idx_audit_action        ON audit_logs(action);
CREATE INDEX idx_audit_created       ON audit_logs(created_at);
CREATE INDEX idx_notifications_user  ON notifications(user_id);
CREATE INDEX idx_notifications_read  ON notifications(is_read);
CREATE INDEX idx_trainreg_user       ON user_training_registrations(user_id);
CREATE INDEX idx_trainreg_session    ON user_training_registrations(session_id);


-- ============================================================
-- 4. SEED DATA  (matches the demo data in HTML files)
-- ============================================================

-- 4.1 Users (default password_hash is a placeholder)
INSERT INTO users (id, email, password_hash, full_name, role, phone, department, status, avatar, joined_at) VALUES
  (1, 'user@mesrs.tn',    SHA2('user', 256),    'Ahmed Ben Ali',    'user',    '+216 71 000 001', 'Finance',     'active', 'AB', '2024-01-15 00:00:00'),
  (2, 'support@mesrs.tn', SHA2('support', 256), 'Sarra Mansour',    'support', '+216 71 000 002', 'IT Support',  'active', 'SM', '2024-02-01 00:00:00'),
  (3, 'admin@mesrs.tn',   SHA2('admin', 256),   'Kamel Fathallah',  'admin',   '+216 71 000 003', 'DIT',         'active', 'KF', '2023-11-20 00:00:00'),
  (4, 'fatma@mesrs.tn',   SHA2('user', 256),    'Fatma Cherif',     'user',    '+216 71 000 004', 'HR',          'active', 'FC', '2024-03-10 00:00:00'),
  (5, 'omar@mesrs.tn',    SHA2('user', 256),    'Omar Tounsi',      'user',    '+216 71 000 005', 'Procurement', 'active', 'OT', '2024-04-05 00:00:00');


-- 4.2 Requests
INSERT INTO requests (id, user_id, type, title, description, category, priority, status, assigned_to, escalated_to_admin, support_response, created_at, updated_at) VALUES
  (1001, 1, 'request',   'Problème de connexion ELISSA',
   'Je ne peux pas accéder au système ELISSA depuis ce matin.',
   'technical', 'high', 'new', 2, 0, '',
   '2025-04-15 08:30:00', '2025-04-15 08:30:00'),

  (1002, 1, 'complaint', 'Retard dans le traitement du courrier',
   'Mon courrier envoyé il y a 2 semaines n''a toujours pas été traité.',
   'access', 'medium', 'in_progress', 2, 0, 'En cours de vérification.',
   '2025-04-10 14:20:00', '2025-04-12 09:00:00'),

  (1003, 4, 'request',   'Demande de formation ELISSA',
   'Je souhaite participer à une formation sur les fonctionnalités avancées d''ELISSA.',
   'training', 'low', 'resolved', 2, 0, 'Formation planifiée.',
   '2025-03-20 10:00:00', '2025-03-25 16:00:00'),

  (1004, 5, 'complaint', 'Erreur dans l''annuaire des correspondants',
   'Mon département n''apparaît pas dans la liste des destinataires.',
   'technical', 'high', 'escalated', 2, 1, '',
   '2025-04-08 09:15:00', '2025-04-14 11:30:00'),

  (1005, 1, 'request',   'Réinitialisation de mot de passe',
   'J''ai oublié mon mot de passe ELISSA et je ne peux pas le réinitialiser.',
   'access', 'medium', 'resolved', 2, 0, 'Mot de passe réinitialisé.',
   '2025-04-01 07:45:00', '2025-04-01 10:00:00'),

  (1006, 4, 'request',   'Accès au module statistiques',
   'Je n''ai pas les droits pour accéder au module statistiques.',
   'access', 'low', 'new', 2, 0, '',
   '2025-04-17 13:00:00', '2025-04-17 13:00:00');


-- 4.3 Request Attachments
INSERT INTO request_attachments (request_id, file_name) VALUES
  (1001, 'capture_erreur.png'),
  (1002, 'courrier_ref.pdf'),
  (1004, 'screenshot.png'),
  (1004, 'email_ref.pdf');


-- 4.4 Messages (conversation threads)
INSERT INTO messages (request_id, sender_type, sender_id, body, created_at) VALUES
  -- Request 1001
  (1001, 'user',    1, 'Le message d''erreur indique "Timeout de connexion".',                               '2025-04-15 08:35:00'),
  -- Request 1002
  (1002, 'user',    1, 'Pouvez-vous vérifier la référence 2025/FIN/0042?',                                  '2025-04-10 14:25:00'),
  (1002, 'support', 2, 'Nous vérifions avec le service concerné. Merci de patienter.',                      '2025-04-12 09:00:00'),
  -- Request 1003
  (1003, 'support', 2, 'Votre inscription à la session du 28 mars est confirmée.',                          '2025-03-22 11:00:00'),
  -- Request 1004
  (1004, 'user',    5, 'Cela bloque l''envoi de tous mes courriers.',                                        '2025-04-08 09:20:00'),
  (1004, 'support', 2, 'Ce problème nécessite une intervention de l''administrateur. Escalade en cours.',    '2025-04-14 11:30:00'),
  (1004, 'system', NULL, 'Demande escaladée à l''administrateur.',                                           '2025-04-14 11:31:00'),
  -- Request 1005
  (1005, 'support', 2, 'Votre mot de passe a été réinitialisé. Vérifiez votre email.',                      '2025-04-01 10:00:00');


-- 4.5 Audit Logs
INSERT INTO audit_logs (id, action, user_id, details, created_at) VALUES
  (1, 'LOGIN',          3, 'Admin logged in',                       '2025-04-17 08:00:00'),
  (2, 'CREATE_REQUEST', 1, 'Request #1001 created',                 '2025-04-15 08:30:00'),
  (3, 'UPDATE_STATUS',  2, 'Request #1002 set to in_progress',      '2025-04-12 09:00:00'),
  (4, 'ESCALATE',       2, 'Request #1004 escalated to admin',      '2025-04-14 11:30:00'),
  (5, 'RESOLVE',        2, 'Request #1003 resolved',                '2025-03-25 16:00:00'),
  (6, 'RESOLVE',        2, 'Request #1005 resolved',                '2025-04-01 10:00:00');


-- 4.6 Training Sessions
INSERT INTO training_sessions (id, title, description, date, duration, created_by) VALUES
  (1, 'Introduction à ELISSA',            'Formation de base pour les nouveaux utilisateurs du système ELISSA.', '2025-05-10', 3, 2),
  (2, 'Gestion avancée des courriers',    'Fonctionnalités avancées: modèles, signatures, workflow.',            '2025-05-15', 4, 2);


-- 4.7 Assist Guides (Knowledge Base)
INSERT INTO assist_guides (id, title, content, category) VALUES
  (1, 'Connexion à ELISSA',  '1. Ouvrir le navigateur\n2. Aller sur elissa.mesrs.tn\n3. Saisir identifiants\n4. Cliquer Connexion', 'technical'),
  (2, 'Envoi de courrier',   '1. Cliquer Nouveau courrier\n2. Remplir les champs\n3. Joindre les documents\n4. Envoyer',            'technical');


-- 4.8 Correspondences
INSERT INTO correspondences (id, title, type, content, created_by) VALUES
  (1, 'Note de service',   'Interne', '', 2),
  (2, 'Lettre officielle', 'Externe', '', 2),
  (3, 'Circulaire',        'Interne', '', 2);


-- 4.9 Referentials
INSERT INTO referentials (type, value) VALUES
  ('subTasks', 'Vérification technique'),
  ('subTasks', 'Mise à jour config'),
  ('subTasks', 'Test de validation'),
  ('subTasks', 'Documentation'),
  ('closureMotives', 'Résolu'),
  ('closureMotives', 'Doublon'),
  ('closureMotives', 'Non reproductible'),
  ('closureMotives', 'Hors périmètre'),
  ('closureMotives', 'Annulé par l''utilisateur');


-- 4.11 Distribution Boxes
INSERT INTO distribution_boxes (id, name) VALUES
  (1, 'Casier DIT'),
  (2, 'Casier Finance'),
  (3, 'Casier RH');

INSERT INTO distribution_box_members (box_id, user_id, member_name) VALUES
  (1, 3, 'Kamel Fathallah'),
  (1, 2, 'Sarra Mansour'),
  (2, 1, 'Ahmed Ben Ali'),
  (3, 4, 'Fatma Cherif');


-- 4.12 Technical Issues
INSERT INTO technical_issues (id, title, description, priority, status, created_by, resolved_at, created_at) VALUES
  (1, 'Serveur lent', 'Serveur ELISSA lent aux heures de pointe',      'high', 'open',     2, NULL,                       '2025-04-10 00:00:00'),
  (2, 'Certificat SSL', 'Certificat SSL expiré sur le portail public', 'medium', 'resolved', 2, '2025-04-01 00:00:00', '2025-03-28 00:00:00');


-- 4.13 System Settings (defaults)
INSERT INTO system_settings (setting_key, setting_value) VALUES
  ('email_notifications', 'enabled'),
  ('session_timeout_minutes', '30'),
  ('default_language', 'fr'),
  ('sla_response_hours', '24'),
  ('sla_resolution_hours', '72'),
  -- Global admin common customization (data.customWidgets + data.workflow)
  ('global_widget_requests', '1'),
  ('global_widget_users', '1'),
  ('global_widget_stats', '1'),
  ('global_widget_activity', '1'),
  ('global_workflow', '');


-- 4.14 Default Dashboard Customizations
INSERT INTO user_customizations (user_id, widget_requests, widget_users, widget_stats, widget_activity) VALUES
  (3, 1, 1, 1, 1);


-- ============================================================
-- 5. USEFUL VIEWS
-- ============================================================

-- View: Summary statistics for the admin dashboard
CREATE OR REPLACE VIEW v_request_stats AS
SELECT
  COUNT(*)                                         AS total_requests,
  SUM(status = 'new')                              AS new_requests,
  SUM(status = 'in_progress')                      AS in_progress,
  SUM(status = 'resolved')                         AS resolved,
  SUM(status = 'escalated')                        AS escalated,
  SUM(status = 'closed')                           AS closed,
  ROUND(SUM(status IN ('resolved','closed')) * 100.0 / NULLIF(COUNT(*), 0), 1) AS resolution_rate_pct
FROM requests;

-- View: Requests with user and assignee names
CREATE OR REPLACE VIEW v_requests_detail AS
SELECT
  r.id,
  r.type,
  r.title,
  r.description,
  r.category,
  r.priority,
  r.status,
  r.escalated_to_admin,
  r.support_response,
  r.closure_motive,
  r.created_at,
  r.updated_at,
  u.full_name   AS requester_name,
  u.email       AS requester_email,
  u.department  AS requester_dept,
  a.full_name   AS assigned_to_name
FROM requests r
  LEFT JOIN users u ON r.user_id     = u.id
  LEFT JOIN users a ON r.assigned_to = a.id;

-- View: Requests grouped by department
CREATE OR REPLACE VIEW v_requests_by_department AS
SELECT
  u.department,
  COUNT(*)                           AS total,
  SUM(r.status = 'new')             AS new_count,
  SUM(r.status = 'in_progress')     AS in_progress_count,
  SUM(r.status = 'resolved')        AS resolved_count,
  SUM(r.status = 'escalated')       AS escalated_count
FROM requests r
  JOIN users u ON r.user_id = u.id
GROUP BY u.department;

-- View: Training sessions with registration counts
CREATE OR REPLACE VIEW v_training_summary AS
SELECT
  t.id,
  t.title,
  t.date,
  t.duration,
  COUNT(tr.user_id)                 AS registered_count
FROM training_sessions t
  LEFT JOIN training_registrations tr ON t.id = tr.session_id AND tr.status = 'registered'
GROUP BY t.id;


-- ============================================================
-- End of sqlpfe.sql
-- ============================================================

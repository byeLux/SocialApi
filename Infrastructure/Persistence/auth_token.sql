-- Applicare manualmente al database dell'applicazione.
-- user.id nel database locale e' TINYINT UNSIGNED: la foreign key usa lo stesso tipo.
CREATE TABLE IF NOT EXISTS auth_token (
    token_hash CHAR(64) NOT NULL,
    user_id TINYINT UNSIGNED NOT NULL,
    expires_at BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (token_hash),

    FOREIGN KEY (user_id)
        REFERENCES user(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

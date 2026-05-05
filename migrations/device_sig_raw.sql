-- device_sig: ampliar de VARCHAR(20) a VARCHAR(120) para formato raw legible
ALTER TABLE user_sessions MODIFY COLUMN device_sig VARCHAR(120) NULL;
ALTER TABLE quote_sessions MODIFY COLUMN device_sig VARCHAR(120) NULL;
ALTER TABLE quote_events MODIFY COLUMN device_sig VARCHAR(120) NULL;
ALTER TABLE cot_feedbacks MODIFY COLUMN device_sig VARCHAR(120) NULL;

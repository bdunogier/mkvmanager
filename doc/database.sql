CREATE TABLE operations_queue (
    hash TEXT PRIMARY KEY,
    type TEXT,
    title TEXT,
    create_time INTEGER,
    start_time INTEGER,
    end_time INTEGER,
    object_string TEXT,
    status INTEGER,
    pid INTEGER,
    message TEXT,
    progress INTEGER );
CREATE INDEX idx_operations_queue_type ON operations_queue(type);
CREATE INDEX idx_operations_queue_status ON operations_queue(status);
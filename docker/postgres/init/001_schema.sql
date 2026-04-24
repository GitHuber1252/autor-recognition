CREATE TABLE IF NOT EXISTS images (
    id UUID PRIMARY KEY,
    kind TEXT NOT NULL CHECK (kind IN ('probe', 'etalon')),
    full_name TEXT,
    original_filename TEXT NOT NULL,
    storage_filename TEXT NOT NULL UNIQUE,
    mime_type TEXT NOT NULL,
    extension TEXT,
    content BYTEA NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_images_kind_created_at
    ON images (kind, created_at DESC);

CREATE TABLE IF NOT EXISTS comparisons (
    id UUID PRIMARY KEY,
    probe_image_id UUID NOT NULL REFERENCES images(id) ON DELETE CASCADE,
    best_etalon_image_id UUID REFERENCES images(id) ON DELETE SET NULL,
    entered_fio TEXT,
    match_probability DOUBLE PRECISION,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_comparisons_probe
    ON comparisons (probe_image_id);

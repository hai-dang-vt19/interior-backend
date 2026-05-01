-- ============================================================
### BẢNG CHÍNH: PRODUCTS
-- ============================================================
CREATE TABLE products (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    -- Định danh
    sku                 VARCHAR(100) NOT NULL UNIQUE,
    name                VARCHAR(255) NOT NULL,

    -- Phân loại
    category_id         UUID REFERENCES categories(id) ON DELETE SET NULL,
    brand_id            UUID REFERENCES brands(id) ON DELETE SET NULL,

    -- Mô tả
    description_short   VARCHAR(500),
    description_long    TEXT,

    -- Phong cách & không gian
    style               VARCHAR(100),            -- Scandinavian, Industrial, Minimalist...
    space_type          VARCHAR(150),            -- phòng khách, văn phòng, nhà hàng...

    -- Xuất xứ
    origin              VARCHAR(100),            -- Việt Nam, Ý, Đan Mạch...
    year_released       SMALLINT,

    -- Trạng thái
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    is_customizable     BOOLEAN NOT NULL DEFAULT FALSE,

    -- Timestamp
    created_at          TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at          TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Index hay dùng
CREATE INDEX idx_products_category  ON products(category_id);
CREATE INDEX idx_products_sku       ON products(sku);
CREATE INDEX idx_products_is_active ON products(is_active);


-- ============================================================
### BẢNG MỞ RỘNG: PRODUCT_VARIANTS
-- 1 sản phẩm → nhiều phiên bản (màu, chất liệu, giá)
-- ============================================================
CREATE TABLE product_variants (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id      UUID NOT NULL REFERENCES products(id) ON DELETE CASCADE,

    -- Định danh riêng của variant
    sku_variant     VARCHAR(120) NOT NULL UNIQUE,

    -- Màu sắc
    color_name      VARCHAR(100),               -- "Walnut Brown", "Matte Black"
    color_hex       CHAR(7),                    -- "#5C3D1E"

    -- Chất liệu
    material_main   VARCHAR(150),               -- Gỗ óc chó, Thép không gỉ...
    material_sub    VARCHAR(150),               -- Đệm mút cao su non, Ốc inox...
    finish          VARCHAR(100),               -- Sơn PU, Veneer, Mạ chrome...

    -- Giá & đơn vị
    price           NUMERIC(15, 2) NOT NULL DEFAULT 0,
    currency        CHAR(3) NOT NULL DEFAULT 'VND',
    unit            VARCHAR(50) DEFAULT 'cái',  -- cái, bộ, cặp...
    qty_per_set     SMALLINT DEFAULT 1,         -- Số lượng trong 1 bộ

    -- Trạng thái
    is_default      BOOLEAN NOT NULL DEFAULT FALSE,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,

    -- Timestamp
    created_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Ràng buộc: mỗi sản phẩm chỉ có 1 variant mặc định
CREATE UNIQUE INDEX idx_variants_one_default
    ON product_variants(product_id)
    WHERE is_default = TRUE;

CREATE INDEX idx_variants_product_id ON product_variants(product_id);
CREATE INDEX idx_variants_sku        ON product_variants(sku_variant);


-- ============================================================
### BẢNG MỞ RỘNG: PRODUCT_SPECS
-- Lưu thông số kỹ thuật dạng key-value → mở rộng linh hoạt
-- Kích thước cố định có cột riêng, thông số đặc thù dùng key-value
-- ============================================================
CREATE TABLE product_specs (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id      UUID NOT NULL REFERENCES products(id) ON DELETE CASCADE,

    -- Kích thước & trọng lượng (cột cố định — hay filter/sort)
    length_mm       NUMERIC(8, 2),
    width_mm        NUMERIC(8, 2),
    height_mm       NUMERIC(8, 2),
    weight_kg       NUMERIC(8, 3),
    max_load_kg     NUMERIC(8, 2),              -- Tải trọng tối đa

    -- Thông số mở rộng theo từng loại sản phẩm (key-value)
    -- Ghế:  seat_height_mm, backrest_height_mm, has_armrest
    -- Đèn:  wattage_w, color_temp_k, cri, ip_rating, voltage_v, dimmable
    -- Tủ:   num_shelves, num_doors, hinge_type
    spec_key        VARCHAR(100),               -- Tên thông số
    spec_value      VARCHAR(255),               -- Giá trị
    spec_unit       VARCHAR(50),                -- Đơn vị (mm, W, K, kg...)

    -- Nhóm thông số (để hiển thị theo section)
    spec_group      VARCHAR(100),               -- "Kích thước", "Điện", "Cơ học"

    -- Thứ tự hiển thị
    sort_order      SMALLINT DEFAULT 0,

    created_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_specs_product_id ON product_specs(product_id);
CREATE INDEX idx_specs_key        ON product_specs(spec_key);

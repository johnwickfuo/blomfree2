export type LandStatus = 'available' | 'sold' | 'reserved';

export interface Land {
    id: number;
    title: string;
    slug: string;
    location_address: string;
    state: string;
    city_or_lga: string;
    number_of_plots: number;
    plot_size_sqm: number;
    price_per_plot: string;
    price_label: string | null;
    description: string;
    features: string[];
    close_to_landmarks: string[] | null;
    has_good_access_road: boolean;
    is_flood_free: boolean;
    installment_available: boolean;
    document_status: string[];
    status: LandStatus;
    is_featured: boolean;
    order: number;
    cover_url: string | null;
    total_price: number;
    gallery_urls?: string[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
}

export type InspectionStatus =
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'completed'
    | 'no_show';

export type Subsidiary = 'collections' | 'gadgets';

export interface CartLineItem {
    id: number;
    cartable_type: string;
    quantity: number;
    max_quantity: number;
    price: number;
    normal_price?: number;
    affiliate_eligible?: boolean;
    affiliate_discount?: number;
    line_total: number;
    name: string;
    variant_label: string | null;
    image: string | null;
    href: string | null;
    issue: string | null;
}

export interface CartAffiliateSummary {
    code: string;
    discount_total: number;
    eligible_item_count: number;
}

export interface CartData {
    count: number;
    subtotal: number;
    items: CartLineItem[];
    has_issues: boolean;
    has_animals: boolean;
    affiliate: CartAffiliateSummary | null;
    applied_affiliate_code: string | null;
}

export interface ProductCardData {
    name: string;
    slug: string;
    category: string;
    cover_url: string | null;
    display_price: number;
    compare_price: string | null;
    is_in_stock: boolean;
    colors: string[];
}

export interface ProductVariantData {
    id: number;
    attributes: Record<string, string>;
    label: string;
    sku: string | null;
    price: number;
    stock: number;
    in_stock: boolean;
}

export interface ProductDetailData {
    id: number;
    name: string;
    slug: string;
    category: string;
    short_description: string;
    description: string;
    base_price: string;
    compare_price: string | null;
    display_price: number;
    has_variants: boolean;
    stock: number | null;
    is_in_stock: boolean;
    attribute_keys: string[];
    gallery_urls: string[];
    cover_url: string | null;
}

export type AnimalListingType = 'individual' | 'pool';

export type AnimalCategory =
    | 'dog'
    | 'cat'
    | 'rabbit'
    | 'grasscutter'
    | 'other';

export type AnimalAvailability =
    | 'available'
    | 'reserved'
    | 'sold'
    | 'on_order';

export interface Animal {
    id: number;
    name: string;
    slug: string;
    listing_type: AnimalListingType;
    category: AnimalCategory;
    breed: string;
    description: string;
    origin: string | null;
    sex: 'male' | 'female' | 'mixed' | null;
    age_text: string | null;
    date_of_birth: string | null;
    typical_adult_size: string | null;
    temperament: string | null;
    vaccination_status: string[] | null;
    parents_info: string | null;
    price: string;
    stock: number | null;
    availability: AnimalAvailability;
    supports_inspection: boolean;
    supports_online_purchase: boolean;
    highlights: string[];
    is_featured: boolean;
    order: number;
    cover_url: string | null;
    effective_availability: AnimalAvailability;
    gallery_urls?: string[];
}

export type TabCategory = 'dog' | 'cat' | 'rabbit' | 'grasscutter';

export type AnimalsByCategory = Record<TabCategory, Animal[]>;

export interface LandFilters {
    state: string | null;
    min_plots: string | number | null;
    price_min: string | number | null;
    price_max: string | number | null;
    installment: boolean;
    status: string;
}

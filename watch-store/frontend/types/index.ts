export interface Product {
  id: number
  name: string
  slug: string
  description: string
  short_description: string
  sku: string
  price: number
  compare_price: number | null
  final_price: number
  discount_percentage: number
  stock_quantity: number
  in_stock: boolean
  is_featured: boolean
  is_new: boolean
  is_best_seller: boolean
  is_limited_edition: boolean
  status: string
  gender: string | null
  movement: string | null
  strap: string | null
  case_material: string | null
  case_diameter: string | null
  case_thickness: string | null
  water_resistance: string | null
  dial_color: string | null
  glass_type: string | null
  warranty_period: string | null
  average_rating: number
  reviews_count: number
  brand: Brand | null
  categories: Category[]
  images: string[]
  thumbnail: string
  meta_title: string | null
  meta_description: string | null
  created_at: string
  updated_at: string
}

export interface Brand {
  id: number
  name: string
  slug: string
  description: string
  logo: string
  is_featured: boolean
  products_count: number
}

export interface Category {
  id: number
  name: string
  slug: string
  description: string
  image: string
  parent_id: number | null
  parent: Category | null
  children: Category[]
  products_count: number
  sort_order: number
}

export interface Order {
  id: number
  order_number: string
  subtotal: number
  shipping_cost: number
  tax: number
  discount: number
  total: number
  status: string
  status_label: string
  payment_method: string
  payment_status: string
  items: OrderItem[]
  shipping_name: string
  shipping_phone: string
  shipping_district: string
  shipping_municipality: string
  shipping_ward: string
  shipping_street: string
  estimated_delivery_date: string | null
  created_at: string
  updated_at: string
}

export interface OrderItem {
  id: number
  product_id: number
  product_name: string
  product_sku: string
  quantity: number
  price: number
  total: number
  product: Product | null
}

export interface CartItem {
  id: number
  product_id: number
  quantity: number
  product: Product
}

export interface Address {
  id: number
  user_id: number
  label: string
  full_name: string
  phone: string
  district: string
  municipality: string
  ward: string
  street: string
  is_default: boolean
}

export interface Review {
  id: number
  product_id: number
  user_id: number
  order_id: number | null
  rating: number
  title: string | null
  comment: string
  status: 'pending' | 'approved' | 'rejected'
  is_approved: boolean
  is_visible_on_homepage: boolean
  is_verified_purchase: boolean
  created_at: string
  updated_at?: string
  product?: Product | null
  user?: { id: number; name: string; email?: string } | null
  order?: { id: number; order_number: string } | null
}

export interface ReviewEligibility {
  purchased: boolean
  has_reviewed: boolean
  can_review: boolean
  review: Review | null
}

export interface Blog {
  id: number
  title: string
  slug: string
  content: string
  excerpt: string
  featured_image: string
  author: string
  meta_title: string
  meta_description: string
  published_at: string
}

export interface HomepageData {
  hero_sliders: HeroSlider[]
  featured_products: Product[]
  new_arrivals: Product[]
  best_sellers: Product[]
  limited_edition: Product[]
  brands: Brand[]
  offers: Offer[]
  latest_blogs: Blog[]
}

export interface HeroSlider {
  id: number
  title: string
  subtitle: string
  description: string
  button_text: string
  button_url: string
  image: string
}

export interface Offer {
  id: number
  label: string | null
  title: string
  description: string | null
  button_text: string | null
  button_url: string | null
  image: string | null
}

export interface ShippingDistrict {
  id: number
  name: string
  cost: number
  delivery_days: string | null
}


export interface User {
  id: number
  name: string
  email: string
  phone: string
}

export interface AuthResponse {
  user: User
  token: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    total: number
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

export interface ProductFilters {
  search?: string
  brand?: string
  category?: string
  gender?: string
  movement?: string
  strap?: string
  case_material?: string
  water_resistance?: string
  min_price?: number
  max_price?: number
  in_stock?: boolean
  is_featured?: boolean
  is_new?: boolean
  is_best_seller?: boolean
  is_limited_edition?: boolean
  sort?: 'newest' | 'popular' | 'best_selling' | 'price_low' | 'price_high'
  per_page?: number
  page?: number
}
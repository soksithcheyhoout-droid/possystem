/**
 * JavaScript functions for Product Operations
 * Frontend examples for getting and deleting products
 */

class ProductOperations {
    constructor(baseUrl = '/api/products') {
        this.baseUrl = baseUrl;
    }

    /**
     * Get all products with optional filters
     */
    async getAllProducts(filters = {}) {
        try {
            const params = new URLSearchParams(filters);
            const response = await fetch(`${this.baseUrl}?${params}`);
            const data = await response.json();
            
            if (data.success) {
                console.log(`Found ${data.count} products`);
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch products');
            }
        } catch (error) {
            console.error('Error fetching products:', error);
            throw error;
        }
    }

    /**
     * Get single product by ID
     */
    async getProduct(id) {
        try {
            const response = await fetch(`${this.baseUrl}/${id}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Product not found');
            }
        } catch (error) {
            console.error(`Error fetching product ${id}:`, error);
            throw error;
        }
    }

    /**
     * Search products
     */
    async searchProducts(query) {
        try {
            const response = await fetch(`${this.baseUrl}/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                console.log(`Found ${data.count} products matching "${query}"`);
                return data.data;
            } else {
                throw new Error(data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Error searching products:', error);
            throw error;
        }
    }

    /**
     * Get products by category
     */
    async getProductsByCategory(categoryId) {
        try {
            const response = await fetch(`${this.baseUrl}/category/${categoryId}`);
            const data = await response.json();
            
            if (data.success) {
                console.log(`Found ${data.count} products in category ${categoryId}`);
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch products by category');
            }
        } catch (error) {
            console.error(`Error fetching products for category ${categoryId}:`, error);
            throw error;
        }
    }

    /**
     * Get low stock products
     */
    async getLowStockProducts() {
        try {
            const response = await fetch(`${this.baseUrl}/low-stock`);
            const data = await response.json();
            
            if (data.success) {
                console.log(`Found ${data.count} low stock products`);
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch low stock products');
            }
        } catch (error) {
            console.error('Error fetching low stock products:', error);
            throw error;
        }
    }

    /**
     * Get product statistics
     */
    async getProductStats() {
        try {
            const response = await fetch(`${this.baseUrl}/stats`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch product stats');
            }
        } catch (error) {
            console.error('Error fetching product stats:', error);
            throw error;
        }
    }

    /**
     * Delete single product
     */
    async deleteProduct(id) {
        try {
            const response = await fetch(`${this.baseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log(`Product ${id} deleted successfully`);
                return true;
            } else {
                throw new Error(data.message || 'Failed to delete product');
            }
        } catch (error) {
            console.error(`Error deleting product ${id}:`, error);
            throw error;
        }
    }

    /**
     * Bulk delete products
     */
    async bulkDeleteProducts(productIds) {
        try {
            const response = await fetch(`${this.baseUrl}/bulk`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ product_ids: productIds })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log(`${data.deleted_count} products deleted successfully`);
                return data.deleted_count;
            } else {
                throw new Error(data.message || 'Failed to bulk delete products');
            }
        } catch (error) {
            console.error('Error bulk deleting products:', error);
            throw error;
        }
    }

    /**
     * Soft delete product (deactivate)
     */
    async softDeleteProduct(id) {
        try {
            const response = await fetch(`${this.baseUrl}/${id}/deactivate`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log(`Product ${id} deactivated successfully`);
                return true;
            } else {
                throw new Error(data.message || 'Failed to deactivate product');
            }
        } catch (error) {
            console.error(`Error deactivating product ${id}:`, error);
            throw error;
        }
    }
}

// Usage Examples
const productOps = new ProductOperations();

// Example usage functions
async function exampleUsage() {
    try {
        // Get all products
        const allProducts = await productOps.getAllProducts();
        console.log('All products:', allProducts);

        // Get active products only
        const activeProducts = await productOps.getAllProducts({ active: true });
        console.log('Active products:', activeProducts);

        // Search for products
        const searchResults = await productOps.searchProducts('milk');
        console.log('Search results:', searchResults);

        // Get products by category
        const categoryProducts = await productOps.getProductsByCategory(1);
        console.log('Category products:', categoryProducts);

        // Get low stock products
        const lowStockProducts = await productOps.getLowStockProducts();
        console.log('Low stock products:', lowStockProducts);

        // Get product statistics
        const stats = await productOps.getProductStats();
        console.log('Product statistics:', stats);

        // Get single product
        const product = await productOps.getProduct(1);
        console.log('Single product:', product);

        // Delete product (uncomment to use)
        // await productOps.deleteProduct(1);

        // Bulk delete products (uncomment to use)
        // await productOps.bulkDeleteProducts([1, 2, 3]);

        // Soft delete product (uncomment to use)
        // await productOps.softDeleteProduct(1);

    } catch (error) {
        console.error('Example usage error:', error);
    }
}

// DOM Helper Functions
function displayProducts(products, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = products.map(product => `
        <div class="product-card" data-id="${product.id}">
            <h3>${product.name}</h3>
            <p>Price: $${product.final_price}</p>
            <p>Stock: ${product.stock_quantity}</p>
            <p>Category: ${product.category?.name || 'No Category'}</p>
            <button onclick="deleteProductById(${product.id})" class="btn-delete">Delete</button>
            <button onclick="deactivateProductById(${product.id})" class="btn-deactivate">Deactivate</button>
        </div>
    `).join('');
}

async function deleteProductById(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        try {
            await productOps.deleteProduct(id);
            // Refresh the product list
            location.reload();
        } catch (error) {
            alert('Failed to delete product: ' + error.message);
        }
    }
}

async function deactivateProductById(id) {
    if (confirm('Are you sure you want to deactivate this product?')) {
        try {
            await productOps.softDeleteProduct(id);
            // Refresh the product list
            location.reload();
        } catch (error) {
            alert('Failed to deactivate product: ' + error.message);
        }
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductOperations;
}
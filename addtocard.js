// Initialize an empty array to store cart items
let cart = [];

// Function to handle "Add to Cart" click event
function addToCart(itemName, itemPrice) {
    // Create a new cart item
    const cartItem = {
        name: itemName,
        price: itemPrice
    };

    // Add the item to the cart array
    cart.push(cartItem);

    // Update the cart display
    updateCartDisplay();
}

// Function to display items in the cart
function updateCartDisplay() {
    const cartContainer = document.getElementById('cart-items');
    cartContainer.innerHTML = ''; // Clear current cart display

    if (cart.length === 0) {
        cartContainer.innerHTML = '<p>Your cart is empty.</p>';
    } else {
        // Loop through the cart and display each item
        cart.forEach((item, index) => {
            const cartItemDiv = document.createElement('div');
            cartItemDiv.classList.add('cart-item');
            cartItemDiv.innerHTML = `
                <p><strong>${item.name}</strong> - $${item.price}</p>
                <button onclick="removeFromCart(${index})">Remove</button>
            `;
            cartContainer.appendChild(cartItemDiv);
        });
    }
}

// Function to remove an item from the cart
function removeFromCart(index) {
    cart.splice(index, 1); // Remove the item at the specified index
    updateCartDisplay(); // Update the cart display
}

// Event listeners for "Add to Cart" buttons
document.querySelectorAll('.add-to-cart-btn').forEach((button, index) => {
    button.addEventListener('click', () => {
        const itemName = button.dataset.name;
        const itemPrice = button.dataset.price;
        addToCart(itemName, itemPrice);
    });
});

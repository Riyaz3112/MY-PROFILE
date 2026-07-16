// firebase-init.js
// Initialize Firebase and set up Realtime Database connection

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js";
import { getDatabase, ref, get, set, push, update, remove, query, orderByChild, equalTo } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-database.js";
import { getAuth, signInAnonymously } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-auth.js";

// Your Firebase config (from Firebase Console)
const firebaseConfig = {
  apiKey: localStorage.getItem('firebaseApiKey') || "YOUR_API_KEY",
  authDomain: localStorage.getItem('firebaseAuthDomain') || "YOUR_PROJECT.firebaseapp.com",
  projectId: localStorage.getItem('firebaseProjectId') || "YOUR_PROJECT_ID",
  storageBucket: localStorage.getItem('firebaseStorageBucket') || "YOUR_PROJECT.appspot.com",
  messagingSenderId: localStorage.getItem('firebaseMessagingSenderId') || "YOUR_MESSAGING_SENDER_ID",
  appId: localStorage.getItem('firebaseAppId') || "YOUR_APP_ID"
};

let app, database, auth;

export async function initFirebase() {
  try {
    app = initializeApp(firebaseConfig);
    database = getDatabase(app);
    auth = getAuth(app);
    
    // Sign in anonymously for public access
    await signInAnonymously(auth);
    console.log('Firebase initialized successfully');
    return { app, database, auth };
  } catch (error) {
    console.error('Firebase initialization error:', error);
    throw error;
  }
}

// Get all products
export async function getProducts() {
  try {
    const response = await fetch('/.netlify/functions/get-products');
    const data = await response.json();
    return data.products || [];
  } catch (error) {
    console.error('Error fetching products:', error);
    return [];
  }
}

// Create order via Netlify Function
export async function createOrder(orderData) {
  try {
    const response = await fetch('/.netlify/functions/create-order', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(orderData)
    });
    
    const data = await response.json();
    if (data.success) {
      return { success: true, orderId: data.orderId };
    }
    return { success: false, message: data.message };
  } catch (error) {
    console.error('Error creating order:', error);
    return { success: false, message: error.message };
  }
}

// Track order
export async function trackOrder(orderId, mobile) {
  try {
    const response = await fetch(`/.netlify/functions/track-order?orderId=${orderId}&mobile=${mobile}`);
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error tracking order:', error);
    return { success: false, message: error.message };
  }
}

// Admin login
export async function adminLogin(username, password) {
  try {
    const response = await fetch('/.netlify/functions/admin-login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    });
    
    const data = await response.json();
    if (data.success) {
      localStorage.setItem('adminToken', data.token);
      return { success: true };
    }
    return { success: false, message: data.message };
  } catch (error) {
    console.error('Error logging in:', error);
    return { success: false, message: error.message };
  }
}

// Check if user is admin
export function isAdmin() {
  return !!localStorage.getItem('adminToken');
}

export { database, auth };

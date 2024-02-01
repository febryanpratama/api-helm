// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.9.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/9.9.0/firebase-analytics.js";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyD8IN97zwhO7ULfa_RBIL0PhFaMQOB4i6I",
  authDomain: "ruang-ajar.firebaseapp.com",
  projectId: "ruang-ajar",
  storageBucket: "ruang-ajar.appspot.com",
  messagingSenderId: "182487778452",
  appId: "1:182487778452:web:44ba401a3666296f87e081",
  measurementId: "G-0BK3TSVBRQ"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

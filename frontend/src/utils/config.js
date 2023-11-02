let baseAPI;
const userAgent = navigator.userAgent;

if (/Win/i.test(userAgent)) {
    baseAPI = "http://backend.test/api"; // Windows
} else if (/Mac/i.test(userAgent)) {
    baseAPI = "http://localhost/api"; // Mac
} else {
    // Default value for other platforms
    baseAPI = "http://backend.test/api";
}

export default {
    baseAPI,
    userId: 1,
};
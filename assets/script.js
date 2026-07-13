const header = document.getElementById("header");
const nav = document.getElementById("nav");
const menuToggle = document.getElementById("menuToggle");
const gpsBtn = document.getElementById("gpsBtn");
const gpsStatus = document.getElementById("gpsStatus");
const locationInput = document.getElementById("location");
const phoneInput = document.getElementById("phone");
const latitudeInput = document.getElementById("latitude");
const longitudeInput = document.getElementById("longitude");
const locationAccuracyInput = document.getElementById("locationAccuracy");
const mapsUrlInput = document.getElementById("mapsUrl");
const serviceSelect = document.getElementById("service");
const appointmentFields = document.getElementById("appointmentFields");
const appointmentDate = document.getElementById("appointmentDate");
const appointmentTime = document.getElementById("appointmentTime");
const submitBtn = document.getElementById("submitBtn");
const yearSelect = document.getElementById("year");
const makeSelect = document.getElementById("make");
const modelSelect = document.getElementById("model");
const emailToggles = document.querySelectorAll(".email-choice-toggle");
const emailModal = document.getElementById("emailModal");
const emailCloseButtons = document.querySelectorAll("[data-email-close]");
const heroSlides = document.querySelectorAll("[data-hero-slide]");
const reviewCards = document.querySelectorAll("[data-review-card]");

const modelsByMake = {
    Toyota: ["Corolla", "Camry", "Prius", "RAV4", "Tacoma"],
    Honda: ["Civic", "Accord", "CR-V", "Pilot", "Odyssey"],
    Ford: ["Focus", "Fusion", "Escape", "Explorer", "F-150"],
    Chevrolet: ["Malibu", "Impala", "Equinox", "Tahoe", "Silverado"],
    Nissan: ["Sentra", "Altima", "Rogue", "Pathfinder", "Frontier"],
    Hyundai: ["Elantra", "Sonata", "Tucson", "Santa Fe", "Kona"],
    Kia: ["Forte", "K5", "Sportage", "Sorento", "Telluride"],
    Jeep: ["Wrangler", "Cherokee", "Grand Cherokee", "Compass", "Gladiator"],
    BMW: ["3 Series", "5 Series", "X3", "X5", "7 Series"],
    Mercedes: ["C-Class", "E-Class", "S-Class", "GLC", "GLE"]
};

function toggleHeader() {
    header.classList.toggle("scrolled", window.scrollY > 20);
}

function fillVehicleSelects() {
    const currentYear = new Date().getFullYear();
    for (let year = currentYear + 1; year >= 1990; year -= 1) {
        yearSelect.add(new Option(year, year));
    }

    Object.keys(modelsByMake).forEach((make) => {
        makeSelect.add(new Option(make, make));
    });
}

function updateModels() {
    modelSelect.innerHTML = "";
    const make = makeSelect.value;
    if (!make || !modelsByMake[make]) {
        modelSelect.add(new Option("Select make first", ""));
        return;
    }

    modelSelect.add(new Option("Model", ""));
    modelsByMake[make].forEach((model) => {
        modelSelect.add(new Option(model, model));
    });
}

function formatAddress(address, displayName = "") {
    if (!address) {
        return displayName;
    }

    const area = address.neighbourhood || address.suburb || address.quarter || address.city_district || address.district;
    const street = [address.house_number, address.road || address.pedestrian || address.footway || address.path]
        .filter(Boolean)
        .join(" ");
    const city = address.city || address.town || address.village || address.hamlet || address.county;
    const state = address.state;
    const zip = address.postcode;

    return [area, street, city, state, zip].filter(Boolean).join(", ") || displayName;
}

async function reverseGeocode(latitude, longitude) {
    const url = new URL("https://nominatim.openstreetmap.org/reverse");
    url.searchParams.set("format", "jsonv2");
    url.searchParams.set("lat", latitude);
    url.searchParams.set("lon", longitude);
    url.searchParams.set("zoom", "18");
    url.searchParams.set("addressdetails", "1");
    url.searchParams.set("accept-language", "en");

    const response = await fetch(url.toString(), {
        headers: {
            Accept: "application/json"
        }
    });

    if (!response.ok) {
        throw new Error("Reverse geocoding failed");
    }

    const data = await response.json();
    return formatAddress(data.address, data.display_name || "");
}

async function reverseGeocodeBackup(latitude, longitude) {
    const url = new URL("https://api.bigdatacloud.net/data/reverse-geocode-client");
    url.searchParams.set("latitude", latitude);
    url.searchParams.set("longitude", longitude);
    url.searchParams.set("localityLanguage", "en");

    const response = await fetch(url.toString(), {
        headers: {
            Accept: "application/json"
        }
    });

    if (!response.ok) {
        throw new Error("Backup reverse geocoding failed");
    }

    const data = await response.json();
    const area = data.locality || data.localityInfo?.administrative?.find((item) => item.adminLevel >= 8)?.name;
    const street = data.localityInfo?.informative?.find((item) => ["road", "street", "route"].includes(item.description?.toLowerCase()))?.name;
    const city = data.city || data.principalSubdivision;
    const fallbackAddress = [area, street, city, data.principalSubdivision, data.postcode]
        .filter(Boolean)
        .join(", ");

    return fallbackAddress || data.localityInfo?.administrative?.map((item) => item.name).filter(Boolean).slice(0, 4).join(", ") || "";
}

async function getReadableAddress(latitude, longitude) {
    try {
        return await reverseGeocode(latitude, longitude);
    } catch (error) {
        return reverseGeocodeBackup(latitude, longitude);
    }
}

function clearGpsFields() {
    latitudeInput.value = "";
    longitudeInput.value = "";
    locationAccuracyInput.value = "";
    mapsUrlInput.value = "";
}

function formatPhoneNumber(value) {
    const digits = value.replace(/\D/g, "").slice(0, 10);

    if (digits.length <= 3) {
        return digits ? `(${digits}` : "";
    }

    if (digits.length <= 6) {
        return `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
    }

    return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
}

function setGpsFields(position) {
    const { latitude, longitude, accuracy } = position.coords;
    const lat = latitude.toFixed(7);
    const lng = longitude.toFixed(7);

    latitudeInput.value = lat;
    longitudeInput.value = lng;
    locationAccuracyInput.value = Number.isFinite(accuracy) ? Math.round(accuracy) : "";
    mapsUrlInput.value = `https://www.google.com/maps?q=${lat},${lng}`;
}

function isBatteryReplacementSelected() {
    return serviceSelect.value === "Battery Replacement (on-site)";
}

function updateAppointmentFields() {
    const showAppointment = isBatteryReplacementSelected();
    appointmentFields.hidden = !showAppointment;
    appointmentDate.required = showAppointment;
    appointmentTime.required = showAppointment;
    submitBtn.innerHTML = showAppointment
        ? '<i class="fa-solid fa-calendar-check"></i> Book Battery Replacement'
        : '<i class="fa-solid fa-paper-plane"></i> Send Request';

    if (!showAppointment) {
        appointmentDate.value = "";
        appointmentTime.value = "";
    }
}

function setMinimumAppointmentDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");
    appointmentDate.min = `${year}-${month}-${day}`;
}

function selectService(service) {
    serviceSelect.value = service;
    updateAppointmentFields();
    document.getElementById("request")?.scrollIntoView({ behavior: "smooth", block: "start" });
}

function setEmailTogglesExpanded(isExpanded) {
    emailToggles.forEach((toggle) => {
        toggle.setAttribute("aria-expanded", String(isExpanded));
    });
}

function openEmailModal() {
    if (!emailModal) {
        return;
    }

    emailModal.hidden = false;
    document.body.classList.add("email-modal-open");
    setEmailTogglesExpanded(true);
    emailModal.querySelector(".email-modal-close")?.focus();
}

function closeEmailModal() {
    if (!emailModal || emailModal.hidden) {
        return;
    }

    emailModal.hidden = true;
    document.body.classList.remove("email-modal-open");
    setEmailTogglesExpanded(false);
}

function startHeroSlider() {
    if (heroSlides.length < 2) {
        return;
    }

    let activeIndex = 0;

    setInterval(() => {
        heroSlides[activeIndex].classList.remove("active");
        activeIndex = (activeIndex + 1) % heroSlides.length;
        heroSlides[activeIndex].classList.add("active");
    }, 4200);
}

function startReviewSliders() {
    reviewCards.forEach((card, cardIndex) => {
        const slides = card.querySelectorAll("[data-review-slide]");
        if (slides.length < 2) {
            return;
        }

        let activeIndex = 0;
        setInterval(() => {
            slides[activeIndex].classList.remove("active");
            activeIndex = (activeIndex + 1) % slides.length;
            slides[activeIndex].classList.add("active");
        }, 5200 + cardIndex * 650);
    });
}

window.addEventListener("scroll", toggleHeader);
toggleHeader();
fillVehicleSelects();
setMinimumAppointmentDate();
updateAppointmentFields();
startHeroSlider();
startReviewSliders();

menuToggle.addEventListener("click", () => {
    const isOpen = nav.classList.toggle("open");
    menuToggle.setAttribute("aria-expanded", String(isOpen));
});

nav.addEventListener("click", (event) => {
    if (event.target.closest("a")) {
        nav.classList.remove("open");
        menuToggle.setAttribute("aria-expanded", "false");
    }
});

makeSelect.addEventListener("change", updateModels);
locationInput.addEventListener("input", clearGpsFields);
phoneInput.addEventListener("input", () => {
    phoneInput.value = formatPhoneNumber(phoneInput.value);
});
phoneInput.addEventListener("blur", () => {
    phoneInput.value = formatPhoneNumber(phoneInput.value);
});
serviceSelect.addEventListener("change", updateAppointmentFields);

emailToggles.forEach((toggle) => {
    toggle.addEventListener("click", (event) => {
        event.preventDefault();
        openEmailModal();
    });
});

emailCloseButtons.forEach((button) => {
    button.addEventListener("click", closeEmailModal);
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeEmailModal();
    }
});

document.querySelectorAll("[data-service]").forEach((button) => {
    button.addEventListener("click", (event) => {
        event.preventDefault();
        selectService(button.dataset.service);
    });
});

document.querySelectorAll("[data-service-card]").forEach((card) => {
    card.addEventListener("click", (event) => {
        if (event.target.closest("a")) {
            return;
        }

        selectService(card.dataset.serviceCard);
    });

    card.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") {
            return;
        }

        event.preventDefault();
        selectService(card.dataset.serviceCard);
    });
});

gpsBtn.addEventListener("click", () => {
    if (!navigator.geolocation) {
        gpsStatus.textContent = "Your browser does not support location detection.";
        return;
    }

    gpsStatus.textContent = "Finding your location...";
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            setGpsFields(position);
            gpsStatus.textContent = "Getting street address...";

            try {
                const address = await getReadableAddress(latitude, longitude);
                if (!address) {
                    throw new Error("No readable address returned");
                }

                locationInput.value = address;
                gpsStatus.className = "success";
                gpsStatus.textContent = `Location detected. Accuracy: about ${locationAccuracyInput.value || "unknown"} meters.`;
            } catch (error) {
                locationInput.value = `GPS: ${latitudeInput.value}, ${longitudeInput.value}`;
                gpsStatus.className = "success";
                gpsStatus.textContent = `Coordinates detected. Accuracy: about ${locationAccuracyInput.value || "unknown"} meters.`;
            }
        },
        () => {
            clearGpsFields();
            gpsStatus.className = "error";
            gpsStatus.textContent = "Could not get location. Please type your address.";
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
});

document.getElementById("vin")?.addEventListener("input", (event) => {
    event.target.value = event.target.value.toUpperCase();
});

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("bk_details_form");
  const subtotalBox = document.getElementById("subtotal");
  const memberCheck = document.getElementById("memberCheck");
  const paySel = document.getElementById("paymentMethod");
  const payExtra = document.getElementById("paymentExtra");

  // Update subtotal dynamically
  function updateSubtotal() {
    const prefs = JSON.parse(localStorage.getItem("prefs") || "[]");
    const member = sessionStorage.getItem("isMember") === "true";
    const pricePer = member ? 11 : 13;
    let total = 0;

    prefs.forEach(p => {
      total += (parseInt(p.pax) || 1) * pricePer;
    });

    subtotalBox.textContent = `Subtotal: $${total.toFixed(2)} (${member ? "Member" : "Standard"} price)`;
    return total;
  }
  updateSubtotal();

  // Payment method handler
  paySel.addEventListener("change", () => {
    payExtra.innerHTML = "";
    if (paySel.value === "PayNow") {
      const rand = Math.floor(Math.random() * 100000);
      payExtra.innerHTML = `
        <div style="text-align:center;margin-top:12px">
          <p>Scan this PayNow QR to complete payment:</p>
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=MiraMooPayNow${rand}" 
               alt="PayNow QR" style="border:1px solid #ddd;border-radius:8px;">
        </div>`;
    } else if (paySel.value === "Card") {
      payExtra.innerHTML = `
        <div class="row" style="margin-top:10px">
          <label>Card Number<input type="text" maxlength="16" pattern="[0-9]{16}" required></label>
          <label>Expiry (MM/YY)<input type="text" maxlength="5" placeholder="MM/YY" required></label>
          <label>CVV<input type="password" maxlength="3" pattern="[0-9]{3}" required></label>
        </div>`;
    }
  });

  /* Membership check
  if (memberCheck) {
    memberCheck.addEventListener("change", async e => {
      if (e.target.checked) {
        const email = form.email.value.trim();
        if (!email) {
          alert("Enter your email before verifying membership.");
          e.target.checked = false;
          return;
        }
        const res = await fetch(`api/check_member.php?email=${encodeURIComponent(email)}`);
        const data = await res.json();
        if (data.isMember) {
          alert(`Welcome back, ${data.name}!`);
          sessionStorage.setItem("isMember", "true");
        } else {
          alert("Membership not found. Please register first.");
          e.target.checked = false;
          sessionStorage.removeItem("isMember");
        }
      } else {
        sessionStorage.removeItem("isMember");
      }
      updateSubtotal();
    });
  } */

  // Submit booking
  form.addEventListener("submit", async e => {
    e.preventDefault();
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const phone = form.phone.value.trim();
    const payment = form.payment.value;
    const prefs = JSON.parse(localStorage.getItem("prefs") || "[]");
    const heldSeatsMap = JSON.parse(sessionStorage.getItem("heldSeatsMap") || "{}");
    const isMember = sessionStorage.getItem("isMember") === "true";
    const total = updateSubtotal();

    if (!name || !email || !phone || !payment) {
      alert("Please complete all fields.");
      return;
    }
    if (!prefs.length) {
      alert("No preferences found.");
      return;
    }

    for (let i = 0; i < prefs.length; i++) {
      if (!heldSeatsMap[i] || !heldSeatsMap[i].length) {
        alert(`Please select seats for ${prefs[i].title}.`);
        return;
      }
    }

    const details = prefs.map((p, i) => ({
      movie: p.title,
      date: p.date,
      time: p.time,
      hall: p.hall,
      pax: p.pax,
      seats: (heldSeatsMap[i] || []).map(sid => {
        const [r, c] = sid.split("-");
        return String.fromCharCode(65 + parseInt(r)) + c;
      })
    }));

    console.log("Booking details:", details);
    const payload = { name, email, phone, payment, isMember, total, details };

    try {
      const res = await fetch("api/insert_booking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        alert("Booking confirmed! Redirecting...");
        localStorage.removeItem("prefs");
        sessionStorage.clear();
        window.location.href = "thankyou.html";
      } else {
        alert("Booking failed. Please try again.");
      }
    } catch (err) {
      console.error(err);
      alert("Error connecting to server.");
    }
  });
});

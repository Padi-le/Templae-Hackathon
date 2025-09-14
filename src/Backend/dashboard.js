

// Check if user is logged in (api_key in localStorage)
// $aaa = '01c0dcf5ad91800cc8881415771420c7f290b5f81205a4bd63473d48170575cf';
// localStorage.setItem("api_key", $aaa);
const apiKey = localStorage.getItem('api_key');
console.log(apiKey);
if (!apiKey) {
    window.location.href = '/Templae-Hackathon/src/Backend/login.php';
}

// Fetch online user count
fetch('/Templae-Hackathon/src/Backend/api.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ type: 'CheckOnline', api_key: apiKey })
})
    .then(res => res.json())
    .then(data => {
        // API returns {status: 'success', data: {online_users: N}}
        document.getElementById('online-count').textContent = (data.data && data.data.online_users) ? data.data.online_users : '0';
    })
    .catch(() => {
        document.getElementById('online-count').textContent = '0';
    });

// Fetch logged-in user info (GetInfo)
fetch('/Templae-Hackathon/src/Backend/api.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ type: 'GetInfo', api_key: apiKey })
})
    .then(res => res.json())
    .then(data => {
        // API returns {status: 'success', data: {user info}}
        if (data.status === 'success' && data.data) {
            const user = data.data;
            document.getElementById('user-info').innerHTML = `
                <div style="font-size:1.2rem;margin-bottom:8px;color:#1976d2;">Welcome, <b>${user.name} ${user.surname}</b></div>
                <div style="font-size:1rem;color:#555;">Email: ${user.email}</div>
                <div style="font-size:1rem;color:#555;">Streak: ${user.streaks ?? 0} days</div>
                <div style="font-size:1rem;color:#555;">Points: ${user.Points ?? 0}</div>
            `;
        }
        // For gender stats, fetch all users (admin endpoint needed), but here we use only the logged-in user as fallback
        let male = 0, female = 0;
        if (data.status === 'success' && data.data) {
            if (data.data.gender === 'male') male = 1;
            if (data.data.gender === 'female') female = 1;
        }
        document.getElementById('male-count').textContent = male;
        document.getElementById('female-count').textContent = female;
    })
    .catch(() => {
        document.getElementById('user-info').textContent = 'Could not load user info.';
        document.getElementById('male-count').textContent = '0';
        document.getElementById('female-count').textContent = '0';
    });

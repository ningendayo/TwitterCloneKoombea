const domain = window.location.hostname;
const protocolWithDomain = `${location.protocol}//${domain}/TwitterCloneKoombea/`;
const wsPart = location.origin;
const config = {
    'serverApi': `${protocolWithDomain}/backend/api.php`
};
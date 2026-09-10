const api_url = 'http://172.17.1.8/BR_client_side/api/';

export async function request(url, options = {}) {
    const res = await fetch(api_url + url, {
        headers: {
            'Content-type': 'application/json',
            'Accept': 'application/json',
        },
        ...options
    })
    const data = await res.json();
    return [res, data];
}

export const api = {
    get: async (url) => {
        return await request(url);
    },
    post: async (url, body) => {
        return await request(url, {
            method: 'POST',
            body: JSON.stringify(body),
        });
    },
}
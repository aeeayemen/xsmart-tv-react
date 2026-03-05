const Storage = {
    set: function (key, val) {
        localStorage.setItem(key, JSON.stringify(val));
    },

    get: function (key) {
        const val = localStorage.getItem(key);
        return val ? JSON.parse(val) : null;
    },

    setStr: function (key, val) {
        localStorage.setItem(key, val);
    },

    getStr: function (key) {
        return localStorage.getItem(key);
    },

    remove: function (key) {
        localStorage.removeItem(key);
    },

    // Favorites Logic
    addFavorite: function (type, item) {
        // type: 'movies', 'series', 'livetv'
        let favs = this.get(`favorites_${type}`) || [];
        if (!favs.find(f => f.stream_id === item.stream_id || f.series_id === item.series_id)) {
            favs.push(item);
            this.set(`favorites_${type}`, favs);
        }
    },

    removeFavorite: function (type, id) {
        let favs = this.get(`favorites_${type}`) || [];
        favs = favs.filter(f => f.stream_id != id && f.series_id != id);
        this.set(`favorites_${type}`, favs);
    },

    isFavorite: function (type, id) {
        let favs = this.get(`favorites_${type}`) || [];
        return !!favs.find(f => f.stream_id == id || f.series_id == id);
    },

    // Resume playback logic
    saveResumeProgress: function (id, timeInSeconds) {
        this.set(`resume_${id}`, timeInSeconds);
    },

    getResumeProgress: function (id) {
        return this.get(`resume_${id}`) || 0;
    },

    // History Logic (Recently Watched)
    addToHistory: function (type, item) {
        let history = this.get(`history_${type}`) || [];
        // Remove if exists to push it to the top
        history = history.filter(h => (h.stream_id && h.stream_id !== item.stream_id) || (h.series_id && h.series_id !== item.series_id) || (!h.stream_id && !h.series_id));
        history.unshift(item); // Add to the beginning

        // Keep only top 15 items
        if (history.length > 15) {
            history = history.slice(0, 15);
        }
        this.set(`history_${type}`, history);
    },

    getHistory: function (type) {
        return this.get(`history_${type}`) || [];
    },

    getCurrentUserId: function () {
        const userInfo = this.get('user_info');
        return userInfo ? userInfo.id : null;
    },

    syncFavoritesWithBackend: async function () {
        const userId = this.getCurrentUserId();
        if (!userId) return;

        const remoteFavs = await API.syncFavorites(userId);
        if (Array.isArray(remoteFavs)) {
            // Group by type and update localStorage
            const grouped = { movie: [], series: [], live: [] };
            remoteFavs.forEach(f => {
                if (grouped[f.type]) {
                    // Normalize back to frontend format if needed
                    grouped[f.type].push({
                        stream_id: f.item_id,
                        series_id: f.item_id, // For compatibility
                        name: f.name,
                        stream_icon: f.image_url,
                        cover: f.image_url
                    });
                }
            });

            this.set('favorites_movie', grouped.movie);
            this.set('favorites_series', grouped.series);
            this.set('favorites_live', grouped.live);
        }
    }
};

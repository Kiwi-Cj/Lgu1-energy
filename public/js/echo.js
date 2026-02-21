(() => {
    if (window.Echo) {
        return;
    }

    // Minimal no-op Echo shim to prevent runtime errors when realtime libs are absent.
    const chain = {
        private: () => chain,
        channel: () => chain,
        listen: () => chain,
        notification: () => chain,
        stopListening: () => chain,
    };

    window.Echo = chain;
})();

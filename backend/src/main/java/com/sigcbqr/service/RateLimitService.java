package com.sigcbqr.service;

import com.sigcbqr.exception.TooManyRequestsException;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Service;

import java.util.ArrayDeque;
import java.util.Deque;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

@Service
public class RateLimitService {

    private final int maxRequests;
    private final long windowMillis;
    private final Map<String, Deque<Long>> hits = new ConcurrentHashMap<>();

    public RateLimitService(@Value("${app.security.rate-limit.max-requests:5}") int maxRequests,
                            @Value("${app.security.rate-limit.window-ms:60000}") long windowMillis) {
        this.maxRequests = maxRequests;
        this.windowMillis = windowMillis;
    }

    public void check(String key) {
        if (key == null || key.isBlank()) {
            return;
        }
        long now = System.currentTimeMillis();
        hits.compute(key, (k, queue) -> {
            if (queue == null) {
                queue = new ArrayDeque<>();
            }
            while (!queue.isEmpty() && now - queue.peekFirst() > windowMillis) {
                queue.pollFirst();
            }
            if (queue.size() >= maxRequests) {
                throw new TooManyRequestsException("Demasiados intentos, intente nuevamente en un minuto");
            }
            queue.addLast(now);
            return queue;
        });
    }

    @Scheduled(fixedDelay = 60_000)
    public void cleanup() {
        long now = System.currentTimeMillis();
        long threshold = now - windowMillis;
        hits.entrySet().removeIf(entry -> entry.getValue().isEmpty() || entry.getValue().peekLast() < threshold);
    }
}
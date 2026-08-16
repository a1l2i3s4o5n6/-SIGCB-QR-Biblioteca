package com.sigcbqr;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.scheduling.annotation.EnableScheduling;

@SpringBootApplication
@EnableScheduling
public class SigcbQrApplication {

    public static void main(String[] args) {
        SpringApplication.run(SigcbQrApplication.class, args);
    }
}

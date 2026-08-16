package com.sigcbqr.model.dto.response;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class ApiResponse {

    private int status;
    private String message;
    private Object data;

    public static ApiResponse success(String message, Object data) {
        return ApiResponse.builder()
                .status(200)
                .message(message)
                .data(data)
                .build();
    }

    public static ApiResponse created(String message, Object data) {
        return ApiResponse.builder()
                .status(201)
                .message(message)
                .data(data)
                .build();
    }

    public static ApiResponse error(int status, String message) {
        return ApiResponse.builder()
                .status(status)
                .message(message)
                .build();
    }
}

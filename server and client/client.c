#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#ifdef _WIN32
    #include <winsock2.h>
    #include <ws2tcpip.h>
#else
    #include <arpa/inet.h>
    #include <sys/socket.h>
    #include <unistd.h>
#endif

#define SERVER_HOST "127.0.0.1"  // Server IP Address
#define SERVER_PORT 8080         // server port number
#define BUFFER_SIZE 1024         // Size of buffer

int main() {
    #ifdef _WIN32
        // init Winsock
        WSADATA wsaData;
        if (WSAStartup(MAKEWORD(2, 2), &wsaData) != 0) {
            printf("WSAStartup failed\n");
            return -1;
        }
    #endif

    SOCKET sock = 0;
    struct sockaddr_in serv_addr;
    char buffer[BUFFER_SIZE] = {0};
    char *query = "SELECT * FROM Utilisateur;";  // SQL query to be sent

    // Create client sockets
    if ((sock = socket(AF_INET, SOCK_STREAM, 0)) == INVALID_SOCKET) {
        printf("Socket creation error: %d\n", WSAGetLastError());
        #ifdef _WIN32
            WSACleanup();
        #endif
        return -1;
    }

    // Set server address information
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_port = htons(SERVER_PORT);

    // Translate IP Addresses
    if (inet_pton(AF_INET, SERVER_HOST, &serv_addr.sin_addr) <= 0) {
        printf("Invalid address/ Address not supported\n");
        #ifdef _WIN32
            closesocket(sock);
            WSACleanup();
        #else
            close(sock);
        #endif
        return -1;
    }

    // Connect to the server
    if (connect(sock, (struct sockaddr *)&serv_addr, sizeof(serv_addr)) == SOCKET_ERROR) {
        printf("Connection Failed with error: %d\n", WSAGetLastError());
        #ifdef _WIN32
            closesocket(sock);
            WSACleanup();
        #else
            close(sock);
        #endif
        return -1;
    }
    printf("Connected to the server at %s:%d\n", SERVER_HOST, SERVER_PORT);

    // Send SQL query to server
    if (send(sock, query, strlen(query), 0) == SOCKET_ERROR) {
        printf("Send failed with error: %d\n", WSAGetLastError());
    } else {
        printf("SQL query sent: %s\n", query);
    }

    // Receive the results returned by the server
    #ifdef _WIN32
        int valread = recv(sock, buffer, BUFFER_SIZE, 0);
    #else
        int valread = read(sock, buffer, BUFFER_SIZE);
    #endif
    
    if (valread > 0) {
        printf("Server response:\n%s\n", buffer);
    } else {
        printf("No response received from server.\n");
    }

    // close socket
    #ifdef _WIN32
        closesocket(sock);
        WSACleanup();
    #else
        close(sock);
    #endif
    
    return 0;
}

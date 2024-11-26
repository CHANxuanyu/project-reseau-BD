import socket
import psycopg2


db_params = {
    "host": "localhost",
    "port": "5432",
    "user": "chan",
    "password": "******",
    "dbname": "Restaurant"
}

# server network parameter
SERVER_HOST = '127.0.0.1'
SERVER_PORT = 8080
BUFFER_SIZE = 1024


def handle_client_query(query):
    """Process client SQL queries and return results"""
    connection = None
    try:
        # Connect to a PostgreSQL Database
        connection = psycopg2.connect(**db_params)
        cursor = connection.cursor()

        # Execute client queries
        cursor.execute(query)
        rows = cursor.fetchall()

        # Format Results
        result = "\n".join([", ".join(map(str, row)) for row in rows])

    except Exception as e:
        result = f"An error occurred: {e}"

    finally:
        # close connection
        if connection:
            cursor.close()
            connection.close()

    return result


def start_server():
    """Starts the server, listens for and handles client connections"""
    server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server_socket.bind((SERVER_HOST, SERVER_PORT))
    server_socket.listen(5)
    print(f"Server listening on {SERVER_HOST}:{SERVER_PORT}")

    try:
        while True:
            client_socket, client_address = server_socket.accept()
            print(f"Accepted connection from {client_address}")

            try:
                # Receive client queries
                query = client_socket.recv(BUFFER_SIZE).decode()
                print(f"Received query: {query}")

                # Process queries and get results
                result = handle_client_query(query)

                # Send results back to client
                client_socket.sendall(result.encode())

            except Exception as e:
                print(f"Error handling client request: {e}")
            finally:
                # close connection
                client_socket.close()
                print(f"Connection to {client_address} closed.")

    except KeyboardInterrupt:
        print("\nServer shutting down.")

    finally:
        # close socket
        server_socket.close()


# start server
if __name__ == "__main__":
    start_server()

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.PrintWriter;
import java.net.Socket;

public class Client {
    private static final String SERVER_HOST = "127.0.0.1";  // 服务器地址
    private static final int SERVER_PORT = 8080;            // 服务器端口
    private static final String QUERY = "SELECT * FROM Utilisateur;";  // 要发送的 SQL 查询

    public static void main(String[] args) {
        Socket socket = null;
        try {
            // 连接服务器
            socket = new Socket(SERVER_HOST, SERVER_PORT);
            System.out.println("Connected to the server at " + SERVER_HOST + ":" + SERVER_PORT);

            // 获取输出流并发送 SQL 查询
            OutputStream output = socket.getOutputStream();
            PrintWriter writer = new PrintWriter(output, true);
            writer.println(QUERY);
            System.out.println("SQL query sent: " + QUERY);

            // 接收服务器返回的结果
            BufferedReader reader = new BufferedReader(new InputStreamReader(socket.getInputStream()));
            String response;
            System.out.println("Server response:");
            while ((response = reader.readLine()) != null) {
                System.out.println(response);
            }
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            try {
                if (socket != null && !socket.isClosed()) {
                    socket.close();  // 关闭连接
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
    }
}

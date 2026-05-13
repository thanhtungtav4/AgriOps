# **Tài liệu yêu cầu nghiệp vụ v1**

## **Hệ thống CRM & Quản lý sản xuất nông nghiệp cho nông trại cung ứng siêu thị**

**Phiên bản: v1**  
 **Mục tiêu: Làm rõ yêu cầu nghiệp vụ để thiết kế hệ thống quản lý nông trại, lập kế hoạch sản xuất, nhật ký canh tác, truy xuất QR và báo cáo cải tiến sau vụ.**

---

# **1\. Bối cảnh hệ thống**

**Hệ thống phục vụ mô hình nông trại cung cấp nông sản cho nhiều hệ thống siêu thị. Nhu cầu cung ứng có thể là đều đặn theo ngày, theo tháng hoặc theo mùa. Một số loại nông sản có thể thu mỗi ngày, một số loại thu theo chu kỳ hoặc theo mùa.**

**Hệ thống cần cho phép người dùng nhập nhu cầu đầu ra, ví dụ:**

* **10kg/ngày**  
* **300kg/tháng**  
* **1.000 trái/tháng**  
* **Một loại rau/củ/quả cụ thể**  
* **Một hợp đồng cung ứng đều đặn**

**Từ nhu cầu này, hệ thống phải tính ngược ra:**

* **Cần bao nhiêu kg/thành phẩm giao khách**  
* **Cần thu thô bao nhiêu**  
* **Cần bao nhiêu cây đang ở giai đoạn thu hoạch**  
* **Cần trồng bao nhiêu cây**  
* **Cần bao nhiêu m2 đất**  
* **Cần bao nhiêu lứa trồng**  
* **Cần bao nhiêu nhân công, giờ công**  
* **Cần bao nhiêu nước, phân, thuốc/sinh học khi phát sinh**  
* **Cần lập lịch chăm sóc, tưới, bón phân, nghiệm thu, thu hoạch**  
* **Cần QR truy xuất theo lô đóng gói**

---

# **2\. Mục tiêu chính**

## **2.1. Mục tiêu nghiệp vụ**

1. **Quản lý nhiều farm, nhiều vùng khí hậu, nhiều lô đất và luống.**  
2. **Quản lý hồ sơ sản xuất của từng loại cây trồng.**  
3. **Từ đơn hàng/hợp đồng cung ứng, tính được kế hoạch trồng.**  
4. **Sinh kế hoạch công việc theo từng giai đoạn sinh trưởng.**  
5. **Theo dõi nhật ký sản xuất thực tế có ảnh, người thực hiện, thời gian.**  
6. **Kiểm soát an toàn khi dùng thuốc/sinh học phát sinh.**  
7. **Quản lý thu hoạch, sơ chế, đóng gói, giao hàng, trả hàng.**  
8. **Tạo QR công khai theo lô đóng gói.**  
9. **Báo cáo năng suất, hao hụt, chi phí và hiệu quả theo farm/cây/lô/vụ.**  
10. **Có dữ liệu thực tế sau mỗi vụ để cải tiến định mức sản xuất.**

## **2.2. Mục tiêu MVP**

**MVP không chỉ tính gần đúng, mà cần đạt mức:**

**Có thống kê thực tế sau mỗi vụ để hiệu chỉnh kế hoạch và định mức cho vụ sau.**

**Tuy nhiên, việc cập nhật định mức nên theo cơ chế:**

**Hệ thống gợi ý điều chỉnh → quản lý duyệt → mới cập nhật vào chuẩn sản xuất.**

**Không nên tự động cập nhật định mức vì nông nghiệp biến động lớn theo thời tiết, mùa vụ, vùng trồng và sâu bệnh.**

---

# **3\. Phạm vi hệ thống**

## **3.1. Trong phạm vi v1**

* **Quản lý farm**  
* **Quản lý lô đất**  
* **Quản lý luống**  
* **Quản lý lịch sử đất**  
* **Quản lý cây trồng**  
* **Quản lý chuẩn thành phẩm nội bộ**  
* **Quản lý chu kỳ sinh trưởng**  
* **Quản lý định mức sản xuất**  
* **Quản lý hợp đồng/nhu cầu cung ứng đều đặn**  
* **Lập kế hoạch sản xuất từ số lượng cần giao**  
* **Sinh lịch công việc canh tác**  
* **Ghi nhận nhật ký sản xuất**  
* **Ghi nhận thuốc/sinh học khi phát sinh**  
* **Nghiệm thu trước thu hoạch**  
* **Thu hoạch**  
* **Sơ chế**  
* **Đóng gói**  
* **QR truy xuất theo lô đóng gói**  
* **Giao hàng**  
* **Biên bản giao nhận**  
* **Quản lý hàng trả về**  
* **Tính chi phí sản xuất**  
* **Báo cáo sau vụ**  
* **Cảnh báo tự động**  
* **Phân quyền người dùng**

## **3.2. Ngoài phạm vi hoặc chưa ưu tiên ở v1**

* **Không cần chuẩn riêng theo từng siêu thị ở MVP.**  
* **Không cần tính bao bì đóng gói trong chi phí MVP.**  
* **Không cần hệ thống tự động chọn farm thay thế; quản lý chọn thủ công.**  
* **Không cần QC riêng sau sơ chế/trước đóng gói, nhưng phiếu đóng gói vẫn cần xác nhận đạt chuẩn.**  
* **Không cần QR hết hạn.**  
* **Không cần tự động cập nhật định mức nếu chưa có quản lý duyệt.**

---

# **4\. Vai trò người dùng**

**Các vai trò chính:**

1. **Admin**  
   * **Quản trị toàn hệ thống**  
   * **Quản lý người dùng, phân quyền, cấu hình dữ liệu nền**  
2. **Chủ farm**  
   * **Xem tổng quan sản xuất, chi phí, sản lượng, hiệu quả**  
   * **Duyệt các thay đổi quan trọng**  
   * **Xem báo cáo nhiều farm**  
3. **Quản lý farm**  
   * **Lập kế hoạch trồng**  
   * **Giao việc**  
   * **Duyệt kế hoạch, nghiệm thu, thu hoạch, đóng gói, giao hàng**  
   * **Theo dõi cảnh báo**  
4. **Kỹ thuật canh tác**  
   * **Thiết lập quy trình cây trồng**  
   * **Theo dõi sâu bệnh**  
   * **Đề xuất xử lý phân/thuốc/sinh học**  
   * **Kiểm tra nghiệm thu trước thu hoạch**  
5. **Nhân công**  
   * **Có tài khoản đăng nhập**  
   * **Xem công việc được giao**  
   * **Báo cáo thực tế công việc**  
   * **Nhập ảnh, thời gian, ghi chú, sản lượng nếu được phân quyền**  
6. **Kho**  
   * **Quản lý vật tư nếu bật module kho**  
   * **Ghi nhận phân, thuốc, giống, vật tư sử dụng**  
   * **Theo dõi chi phí vật tư**  
7. **Giao hàng**  
   * **Xem phiếu giao hàng**  
   * **Ghi nhận giao nhận**  
   * **Upload ảnh/chứng từ**  
   * **Ghi nhận hàng bị trả nếu có**

---

# **5\. Các khái niệm nghiệp vụ chính**

## **5.1. Farm**

**Farm là đơn vị sản xuất. Hệ thống có nhiều farm, có thể ở nhiều vùng khí hậu khác nhau.**

**Thông tin farm:**

* **Tên farm**  
* **Mã farm**  
* **Địa chỉ/khu vực**  
* **Vùng khí hậu**  
* **Người phụ trách**  
* **Diện tích tổng**  
* **Trạng thái hoạt động**  
* **Chứng nhận nếu có**  
* **Hình ảnh farm**

## **5.2. Lô đất**

**Mỗi farm có nhiều lô đất.**

**Thông tin lô đất:**

* **Mã lô**  
* **Tên lô**  
* **Farm trực thuộc**  
* **Diện tích m2**  
* **Loại đất**  
* **Nguồn nước**  
* **Trạng thái đất**  
* **Cây/lứa hiện tại**  
* **Ghi chú**

**Trạng thái đất:**

* **Đang trống**  
* **Đang chuẩn bị đất**  
* **Đang trồng**  
* **Đang thu hoạch**  
* **Đang nghỉ đất**  
* **Đang cải tạo đất**  
* **Tạm ngưng sử dụng**

## **5.3. Luống**

**Một lô đất có thể chia thành nhiều luống.**

**Thông tin luống:**

* **Mã luống**  
* **Lô đất**  
* **Chiều dài**  
* **Chiều rộng**  
* **Diện tích**  
* **Số cây dự kiến**  
* **Trạng thái**  
* **Ghi chú**

**MVP có thể dùng luống để tính toán diện tích và số cây. Sau này có thể quản lý chi tiết năng suất/sâu bệnh theo từng luống.**

## **5.4. Lịch sử đất**

**Cần lưu lịch sử đất để phục vụ cải tiến và cảnh báo.**

**Thông tin lịch sử:**

* **Vụ trước trồng cây gì**  
* **Lứa trồng nào**  
* **Thời gian trồng**  
* **Sản lượng thực tế**  
* **Tỷ lệ hao hụt**  
* **Sâu bệnh từng gặp**  
* **Phân/thuốc đã dùng**  
* **Thời gian nghỉ đất**  
* **Lần cải tạo đất gần nhất**  
* **Ghi chú bất thường**

---

# **6\. Hồ sơ cây trồng**

**Mỗi loại cây cần có một hồ sơ sản xuất để làm cơ sở tính kế hoạch.**

## **6.1. Thông tin cây trồng**

* **Tên cây**  
* **Nhóm cây:**  
  * **Rau ăn lá**  
  * **Rau ăn quả**  
  * **Củ**  
  * **Cây ăn trái**  
* **Đơn vị bán:**  
  * **kg**  
  * **trái**  
  * **bó**  
  * **thùng**  
* **Đơn vị sản xuất chính:**  
  * **cây**  
  * **m2**  
  * **luống**  
* **Có thu nhiều lần không**  
* **Có nhiều chu kỳ trong năm không**  
* **Thời gian sinh trưởng trung bình**  
* **Thời gian khai thác thu hoạch**  
* **Thời gian nghỉ đất/cải tạo đất**

## **6.2. Chuẩn thành phẩm nội bộ**

**Siêu thị chưa có chuẩn riêng trong MVP. Hệ thống dùng chuẩn nội bộ của nông trại.**

**Thông tin chuẩn thành phẩm:**

* **Trọng lượng**  
* **Kích thước**  
* **Màu sắc**  
* **Độ đồng đều**  
* **Độ non/già**  
* **Tỷ lệ lỗi cho phép**  
* **Quy cách đóng gói:**  
  * **500g**  
  * **1kg**  
  * **thùng 10kg**  
* **Phân loại:**  
  * **Loại A**  
  * **Loại B**  
  * **Loại C**  
  * **Loại bỏ**

---

# **7\. Chu kỳ sinh trưởng**

## **7.1. Giai đoạn mặc định**

**Các giai đoạn chuẩn:**

1. **Làm đất**  
2. **Gieo/trồng**  
3. **Cây con**  
4. **Sinh trưởng**  
5. **Ra hoa**  
6. **Nuôi trái / tạo củ / tạo lá thương phẩm**  
7. **Thu hoạch**  
8. **Cải tạo đất**

**Không ép mọi cây phải có đủ tất cả giai đoạn.**

**Ví dụ:**

* **Rau ăn lá: làm đất → gieo/trồng → cây con → sinh trưởng → thu hoạch → cải tạo đất**  
* **Dưa leo/cà chua/ớt: làm đất → trồng → cây con → sinh trưởng → ra hoa → nuôi trái → thu hoạch nhiều lần → cải tạo đất**  
* **Củ: làm đất → gieo/trồng → cây con → sinh trưởng → tạo củ → thu hoạch → cải tạo đất**

## **7.2. Cách quản lý thời gian**

**Mỗi giai đoạn quản lý theo khoảng ngày.**

**Ví dụ:**

* **Cây con: ngày 1–7**  
* **Sinh trưởng: ngày 8–20**  
* **Ra hoa: ngày 21–30**  
* **Nuôi trái: ngày 31–45**  
* **Thu hoạch: ngày 46–75**

**Mỗi giai đoạn cần có:**

* **Ngày bắt đầu dự kiến**  
* **Ngày kết thúc dự kiến**  
* **Có thể điều chỉnh thực tế**  
* **Lý do điều chỉnh**

## **7.3. Thông tin trong từng giai đoạn**

**Mỗi giai đoạn cần lưu:**

* **Tên giai đoạn**  
* **Mục tiêu giai đoạn**  
* **Tình trạng cây kỳ vọng**  
* **Công việc cần làm**  
* **Lịch tưới**  
* **Nhu cầu nước**  
* **Kế hoạch phân bón**  
* **Thuốc/sinh học nếu có phát sinh**  
* **Định mức nhân công**  
* **Tiêu chí kiểm tra**  
* **Có cần chụp ảnh không**  
* **Có cần quản lý duyệt không**  
* **Rủi ro thường gặp**  
* **Cách xử lý bất thường**

---

# **8\. Lịch tưới, phân và thuốc/sinh học**

## **8.1. Lịch tưới**

**Lịch tưới là cố định theo loại cây và giai đoạn.**

**Thông tin cần lưu:**

* **Giai đoạn**  
* **Tần suất tưới**  
* **Lượng nước dự kiến**  
* **Đơn vị tính:**  
  * **lít/m2/ngày**  
  * **lít/cây/ngày**  
  * **lít/luống/ngày**  
* **Thời điểm tưới**  
* **Yêu cầu ghi nhận thực tế**

**Khi tạo lứa trồng, hệ thống tự sinh lịch tưới dự kiến.**

## **8.2. Phân bón**

**Phân bón có kế hoạch theo loại cây và giai đoạn.**

**Thông tin kế hoạch:**

* **Giai đoạn áp dụng**  
* **Loại phân**  
* **Định mức**  
* **Đơn vị tính**  
* **Ngày/khoảng ngày thực hiện**  
* **Ghi chú kỹ thuật**

**Thông tin thực tế:**

* **Ngày bón**  
* **Loại phân**  
* **Số lượng**  
* **Lô/lứa áp dụng**  
* **Người thực hiện**  
* **Ảnh**  
* **Ghi chú**  
* **Chi phí nếu có**

## **8.3. Thuốc/sinh học**

**Thuốc/sinh học chỉ ghi nhận khi phát sinh, không sinh lịch cố định.**

**Flow:**

1. **Phát hiện sâu bệnh/bất thường**  
2. **Tạo ghi nhận phát sinh**  
3. **Chọn biện pháp xử lý**  
4. **Ghi thuốc/sinh học nếu có dùng**  
5. **Nhập liều lượng**  
6. **Nhập ảnh**  
7. **Nhập người xử lý**  
8. **Ghi nhận thời gian cách ly nếu có**  
9. **Cảnh báo nếu chưa đủ thời gian cách ly mà chuẩn bị thu hoạch**

**Khuyến nghị MVP vẫn nên có danh mục thuốc/sinh học:**

* **Tên thuốc/chế phẩm**  
* **Loại**  
* **Hoạt chất**  
* **Cây trồng được phép dùng**  
* **Liều lượng khuyến nghị**  
* **Thời gian cách ly**  
* **Trạng thái được phép/cấm dùng**

---

# **9\. Mô hình thu hoạch**

## **9.1. Cây thu một lần**

**Áp dụng cho một số rau ăn lá, củ, cây ngắn ngày.**

**Cần lưu:**

* **Ngày thu dự kiến**  
* **Năng suất tối thiểu/trung bình/tối đa**  
* **Đơn vị năng suất:**  
  * **kg/m2**  
  * **kg/cây**  
  * **kg/luống**  
* **Tỷ lệ loại A/B/C/loại bỏ**  
* **Hao hụt sau thu hoạch**  
* **Thời gian kết thúc lứa**

## **9.2. Cây thu nhiều lần**

**Áp dụng cho dưa leo, cà chua, ớt, khổ qua và các cây có khai thác nhiều lần.**

**Người dùng chọn nhập năng suất theo:**

**Đầu vụ / chính vụ / cuối vụ**

**Mỗi giai đoạn thu cần lưu:**

* **Tên giai đoạn thu**  
* **Khoảng ngày**  
* **Lịch thu:**  
  * **mỗi ngày**  
  * **2 ngày/lần**  
  * **3 ngày/lần**  
  * **tùy loại cây**  
* **Năng suất tối thiểu/trung bình/tối đa**  
* **Hệ số năng suất so với chính vụ**  
* **Tỷ lệ loại A/B/C/loại bỏ**

**Ví dụ:**

* **Đầu vụ: ngày 35–45, hệ số 40%**  
* **Chính vụ: ngày 46–70, hệ số 100%**  
* **Cuối vụ: ngày 71–85, hệ số 50%**

---

# **10\. Định mức sản xuất**

## **10.1. Hướng tính chính**

**Hệ thống ưu tiên hướng:**

**m2 → số cây → sản lượng**

**Tức là:**

1. **Từ diện tích m2**  
2. **Tính ra số cây theo mật độ trồng**  
3. **Tính sản lượng theo cây/m2/giai đoạn**  
4. **Trừ hao hụt**  
5. **Ra sản lượng thành phẩm giao khách**

## **10.2. Chỉ số cần tính**

**Hệ thống cần tính được:**

* **Kg giao khách/ngày**  
* **Kg cần thu thô/ngày**  
* **Số trái cần thu/ngày nếu sản phẩm tính theo trái**  
* **Số cây đang thu/ngày**  
* **Số cây cần trồng/ngày**  
* **Số cây cần trồng trong cả chu kỳ**  
* **M2 đất cần dùng/ngày**  
* **M2 đất cần dùng cho cả chu kỳ**  
* **Số lứa trồng cần duy trì**  
* **Số ngày trước khi có thể giao hàng**  
* **Nhu cầu nhân công theo công và giờ**  
* **Nhu cầu nước**  
* **Nhu cầu phân bón**  
* **Dự kiến chi phí**

---

# **11\. Công thức tính kế hoạch từ đơn hàng**

## **11.1. Đầu vào**

**Đơn hàng/hợp đồng cung ứng:**

* **Khách hàng**  
* **Loại nông sản**  
* **Số lượng cần giao**  
* **Đơn vị:**  
  * **kg**  
  * **trái**  
  * **bó**  
  * **thùng**  
* **Tần suất:**  
  * **mỗi ngày**  
  * **mỗi tuần**  
  * **mỗi tháng**  
  * **theo mùa**  
* **Ngày bắt đầu**  
* **Ngày kết thúc**  
* **Quy cách thành phẩm**

## **11.2. Số lượng giao là thành phẩm**

**Quy ước:**

**Số lượng đơn hàng là số lượng thành phẩm đóng gói giao khách.**

**Ví dụ:**

* **Khách cần 10kg**  
* **Đây là 10kg sau sơ chế/đóng gói, đạt chuẩn giao siêu thị**

## **11.3. Tính sản lượng cần thu thô**

**Công thức cơ bản:**

**Sản lượng cần thu thô**  
**\= Sản lượng giao khách / Tỷ lệ còn lại sau hao hụt**

**Hao hụt gồm:**

* **Hao hụt thu hoạch**  
* **Hao hụt sơ chế**  
* **Hao hụt đóng gói nếu có**  
* **Tỷ lệ không đạt loại A**  
* **Tỷ lệ loại bỏ**

**Ví dụ:**

**Giao khách: 10kg**  
**Hao hụt tổng: 20%**

**Sản lượng cần thu thô \= 10 / (1 \- 0.2) \= 12.5kg**

## **11.4. Tính số cây cần đang thu**

**Ví dụ:**

**Cần thu thô: 12.5kg/ngày**  
**Trọng lượng trung bình: 0.25kg/trái**  
**Cần thu: 50 trái/ngày**

**Nếu chính vụ 1 cây cho 2 trái/ngày:**  
**Cần 25 cây đang ở giai đoạn chính vụ**

**Nếu đang đầu vụ/cuối vụ thì dùng hệ số năng suất khác.**

## **11.5. Tính số cây cần trồng ban đầu**

**Số cây cần trồng**  
**\= Số cây cần đạt thu / Tỷ lệ sống**

**Ví dụ:**

**Cần 25 cây đang thu**  
**Tỷ lệ sống: 90%**

**Số cây cần trồng \= 25 / 0.9 ≈ 28 cây**

## **11.6. Tính diện tích đất**

**Diện tích cần dùng**  
**\= Số cây cần trồng / Mật độ cây trên m2**

**Ví dụ:**

**Cần trồng: 28 cây**  
**Mật độ: 3 cây/m2**

**Diện tích \= 28 / 3 ≈ 9.3m2**

**Cần cộng thêm hệ số an toàn nếu farm muốn trồng dư.**

---

# **12\. Kế hoạch sản xuất**

**Khi nhập số lượng cần giao, hệ thống phải trả ra kế hoạch:**

* **Cây trồng**  
* **Farm đề xuất hoặc farm được chọn**  
* **Lô đất/luống sử dụng**  
* **Diện tích cần dùng**  
* **Số cây cần trồng**  
* **Số lứa cần tạo**  
* **Ngày bắt đầu làm đất**  
* **Ngày gieo/trồng**  
* **Ngày bắt đầu thu**  
* **Lịch thu hoạch**  
* **Sản lượng dự kiến theo ngày/tháng**  
* **Lịch tưới**  
* **Lịch phân bón**  
* **Công việc chăm sóc**  
* **Nhân công dự kiến**  
* **Nước dự kiến**  
* **Phân dự kiến**  
* **Chi phí dự kiến**  
* **Cảnh báo rủi ro nếu có**

---

# **13\. Công việc và nhân công**

## **13.1. Đơn vị nhân công**

**Nhân công quản lý theo cả:**

* **Số công**  
* **Số giờ**

**Ví dụ:**

**1 công \= 8 giờ**  
**Thu hoạch: 1 công / 80kg**  
**Sơ chế: 1 công / 100kg**  
**Làm đất: 1 công / 500m2**

## **13.2. Công việc tính tùy loại**

**Mỗi công việc có đơn vị tính riêng:**

* **Làm đất: theo m2/luống**  
* **Gieo/trồng: theo số cây/m2**  
* **Tưới: theo m2/cây/giai đoạn**  
* **Bón phân: theo cây/m2/luống**  
* **Kiểm tra sâu bệnh: theo lô/m2**  
* **Làm giàn: theo cây/luống**  
* **Tỉa lá: theo cây**  
* **Thu hoạch: theo kg/trái**  
* **Sơ chế: theo kg**  
* **Cải tạo đất: theo m2**

## **13.3. Phiếu công việc**

**Mỗi công việc cần có:**

* **Tên công việc**  
* **Giai đoạn áp dụng**  
* **Lứa trồng**  
* **Farm/lô/luống**  
* **Người/nhóm phụ trách**  
* **Ngày dự kiến**  
* **Thời gian dự kiến**  
* **Số công dự kiến**  
* **Số giờ dự kiến**  
* **Vật tư liên quan**  
* **Checklist**  
* **Ảnh bắt buộc nếu có**  
* **Trạng thái:**  
  * **Chưa làm**  
  * **Đang làm**  
  * **Đã báo cáo**  
  * **Chờ duyệt**  
  * **Hoàn tất**  
  * **Trễ hạn**  
  * **Hủy**

---

# **14\. Nhật ký sản xuất**

## **14.1. Kế hoạch và thực tế**

**Hệ thống phải tách rõ:**

* **Kế hoạch**  
* **Thực tế**

**Ví dụ:**

**Kế hoạch:**

* **Ngày 1 tưới**  
* **Ngày 7 bón phân**  
* **Ngày 15 kiểm tra sâu bệnh**  
* **Ngày 30 nghiệm thu**  
* **Ngày 35 thu hoạch**

**Thực tế:**

* **Ai thực hiện**  
* **Thực hiện lúc nào**  
* **Số lượng nước/phân/thuốc**  
* **Ảnh**  
* **Ghi chú**  
* **Tình trạng cây**  
* **Phát sinh/hư hại**

## **14.2. Nhật ký cần lưu**

* **Lịch tưới thực tế**  
* **Lịch bón phân thực tế**  
* **Ghi nhận sâu bệnh**  
* **Ghi nhận thuốc/sinh học nếu phát sinh**  
* **Ghi nhận ảnh lô trồng**  
* **Ghi nhận tình trạng cây**  
* **Ghi nhận hư hại**  
* **Ghi nhận thay đổi giai đoạn sinh trưởng**  
* **Ghi nhận nghiệm thu trước thu hoạch**

---

# **15\. Nghiệm thu trước thu hoạch**

**Trước khi thu hoạch phải có bước nghiệm thu.**

**Checklist nghiệm thu:**

* **Kích thước đạt chuẩn chưa**  
* **Trọng lượng/size đạt chưa**  
* **Màu sắc/cảm quan đạt chưa**  
* **Tỷ lệ sâu bệnh/dập hư**  
* **Ảnh lô trồng**  
* **Ngày sử dụng thuốc gần nhất**  
* **Đã đủ thời gian cách ly chưa**  
* **Dự kiến sản lượng thu được**  
* **Tỷ lệ loại A/B/C dự kiến**  
* **Quản lý duyệt cho thu hoạch**

**Nếu chưa đạt, hệ thống cần cảnh báo hoặc không cho tạo lệnh thu hoạch chính thức tùy cấu hình.**

---

# **16\. Thu hoạch**

**Phiếu thu hoạch cần có:**

* **Ngày thu hoạch**  
* **Farm**  
* **Lô đất**  
* **Luống nếu có**  
* **Lứa trồng**  
* **Cây trồng**  
* **Sản lượng thô**  
* **Sản lượng loại A**  
* **Sản lượng loại B**  
* **Sản lượng loại C**  
* **Sản lượng loại bỏ**  
* **Số trái nếu cần**  
* **Ảnh thu hoạch**  
* **Người ghi nhận**  
* **Ghi chú**

## **16.1. Lý do hàng không đạt**

**Danh mục lỗi:**

* **Quá size**  
* **Non**  
* **Già**  
* **Sâu bệnh**  
* **Dập nát**  
* **Cong/vẹo**  
* **Không đều màu**  
* **Nứt trái**  
* **Thối/hư**  
* **Dính đất/bẩn**  
* **Sai trọng lượng**  
* **Không đạt cảm quan**  
* **Khác**

**Mỗi lỗi cần lưu:**

* **Số lượng/kg bị lỗi**  
* **Tỷ lệ lỗi**  
* **Ảnh nếu cần**  
* **Ghi chú nguyên nhân**

---

# **17\. Sơ chế**

**Sau thu hoạch có bước sơ chế.**

**Các thao tác có thể gồm:**

* **Rửa**  
* **Cắt gốc**  
* **Loại lá hư**  
* **Loại trái lỗi**  
* **Làm sạch đất/bùn**  
* **Phân loại A/B/C**  
* **Cân lại sản lượng**

**Phiếu sơ chế cần lưu:**

* **Lô thu hoạch**  
* **Sản lượng trước sơ chế**  
* **Sản lượng sau sơ chế**  
* **Sản lượng loại A/B/C**  
* **Sản lượng loại bỏ**  
* **Lý do loại bỏ**  
* **Người thực hiện**  
* **Thời gian**  
* **Ảnh nếu cần**  
* **Ghi chú**

**Không có bước QC riêng sau sơ chế, nhưng phiếu sơ chế/đóng gói nên có xác nhận tối thiểu:**

* **Đạt chuẩn đóng gói: có/không**  
* **Ghi chú lỗi**  
* **Ảnh sau sơ chế**  
* **Người xác nhận**

---

# **18\. Đóng gói**

## **18.1. Lô đóng gói**

**QR được tạo theo lô đóng gói.**

**Một lô thu hoạch có thể chia ra nhiều lô đóng gói.**

**Một lô đóng gói có thể trộn từ nhiều lô thu hoạch/nhiều farm.**

**Vì vậy quan hệ cần là many-to-many:**

**Harvest Lot ↔ Packing Lot**

**Lô đóng gói cần lưu:**

* **Mã lô đóng gói**  
* **Ngày đóng gói**  
* **Cây trồng/sản phẩm**  
* **Tổng số lượng**  
* **Đơn vị**  
* **Nguồn từ các lô thu hoạch**  
* **Tỷ lệ/số lượng từng nguồn nếu có**  
* **Người đóng gói**  
* **Ảnh**  
* **Trạng thái**  
* **QR truy xuất**

## **18.2. Trộn nhiều nguồn**

**Ví dụ:**

**Packing Lot P-001: 10kg dưa leo**

**Nguồn:**  
**\- Farm A / lô A1 / lứa DL-A1-01: 6kg**  
**\- Farm B / lô B2 / lứa DL-B2-03: 4kg**

**QR công khai cần hiển thị được nhiều nguồn.**

---

# **19\. QR truy xuất**

## **19.1. Đơn vị QR**

**QR tạo theo lô đóng gói.**

**QR không hết hạn.**

## **19.2. Nội dung QR công khai**

**QR công khai hiển thị:**

* **Tên nông trại**  
* **Lô đóng gói**  
* **Lô trồng/farm nguồn**  
* **Ngày gieo/trồng**  
* **Ngày thu hoạch**  
* **Quy trình chăm sóc tóm tắt**  
* **Chứng nhận nếu có**  
* **Hình ảnh**

**Không nên hiển thị công khai:**

* **Tên thuốc chi tiết**  
* **Liều lượng thuốc chi tiết**  
* **Người thực hiện nội bộ**  
* **Ghi chú lỗi nhạy cảm**  
* **Chi phí sản xuất**

**Có thể hiển thị trạng thái an toàn:**

* **Có nhật ký canh tác**  
* **Tuân thủ kiểm soát an toàn**  
* **Đã nghiệm thu trước thu hoạch**

---

# **20\. Giao hàng và biên bản giao nhận**

**Khi giao hàng cho siêu thị, cần ghi nhận biên bản giao nhận.**

**Phiếu giao hàng cần có:**

* **Mã giao hàng**  
* **Khách hàng/siêu thị**  
* **Ngày giao**  
* **Farm/nguồn hàng**  
* **Lô đóng gói**  
* **Mã QR lô đóng gói**  
* **Số lượng dự kiến giao**  
* **Số lượng thực giao**  
* **Số lượng khách nhận**  
* **Số lượng bị trả nếu có**  
* **Lý do trả hàng**  
* **Ảnh chứng từ/biên bản**  
* **Người giao**  
* **Người nhận**  
* **Ghi chú**

**Flow:**

**Đóng gói**  
**→ tạo QR theo lô đóng gói**  
**→ tạo phiếu giao hàng**  
**→ giao cho siêu thị**  
**→ ghi nhận biên bản giao nhận**  
**→ nếu có trả hàng thì tạo phiếu trả hàng**  
**→ cập nhật báo cáo hao hụt/lỗi/chất lượng**

---

# **21\. Hàng trả về**

**Có quản lý hàng bị trả về.**

**Phiếu trả hàng cần có:**

* **Khách hàng/siêu thị**  
* **Ngày trả hàng**  
* **Lô đóng gói**  
* **Số lượng bị trả**  
* **Lý do trả**  
* **Ảnh bằng chứng**  
* **Người ghi nhận**  
* **Hướng xử lý**  
* **Ghi chú**

**Lý do trả hàng:**

* **Dập/héo**  
* **Sai size**  
* **Sai trọng lượng**  
* **Không đạt màu sắc**  
* **Không đồng đều**  
* **Sâu bệnh**  
* **Bao gói lỗi**  
* **Giao trễ**  
* **Thiếu số lượng**  
* **Khác**

**Hướng xử lý:**

* **Hủy bỏ**  
* **Bán kênh khác**  
* **Đưa về sơ chế lại**  
* **Ghi nhận hao hụt**  
* **Đền bù/bù hàng lần sau**

---

# **22\. Điều phối nhiều farm**

**Nếu farm chính thiếu hàng, quản lý chọn farm khác bổ sung thủ công.**

**Hệ thống cần:**

* **Cảnh báo thiếu hàng**  
* **Hiển thị sản lượng khả dụng ở farm khác**  
* **Cho quản lý chọn farm bổ sung**  
* **Ghi nhận số lượng lấy từ farm nào**  
* **Tạo QR riêng theo nguồn farm/lô đóng gói**  
* **Nếu trộn nhiều nguồn, QR phải hiển thị nhiều nguồn**

**Không cần tự động tối ưu farm ở MVP.**

---

# **23\. Phê duyệt**

**Các bước cần có phê duyệt:**

1. **Duyệt kế hoạch trồng**  
2. **Duyệt thay đổi kế hoạch quan trọng**  
3. **Duyệt dùng thuốc/sinh học nếu cần**  
4. **Duyệt nghiệm thu trước thu hoạch**  
5. **Duyệt thu hoạch nếu có điều kiện nghiệm thu**  
6. **Duyệt đóng gói**  
7. **Duyệt giao hàng**  
8. **Duyệt cập nhật định mức sau vụ**

**Mỗi phê duyệt nên lưu:**

* **Người yêu cầu**  
* **Người duyệt**  
* **Thời gian duyệt**  
* **Trạng thái**  
* **Ghi chú**  
* **Ảnh/tài liệu đính kèm nếu có**

---

# **24\. Cảnh báo tự động**

**Hệ thống cần cảnh báo:**

* **Tới lịch tưới**  
* **Trễ việc**  
* **Chưa hoàn thành công việc bắt buộc**  
* **Chưa đủ thời gian cách ly**  
* **Chuẩn bị đến ngày thu hoạch**  
* **Chưa nghiệm thu trước thu hoạch**  
* **Thiếu sản lượng so với kế hoạch**  
* **Thiếu nhân công**  
* **Lô đất chưa sẵn sàng**  
* **Lô đất đang nghỉ/cải tạo nhưng bị chọn trồng**  
* **Tỷ lệ hao hụt cao bất thường**  
* **Tỷ lệ hàng trả cao**  
* **Năng suất thấp hơn định mức**  
* **Chi phí vượt dự kiến**

---

# **25\. Chi phí sản xuất**

**Có tính chi phí sản xuất.**

**Chi phí cần tổng hợp theo lứa trồng:**

* **Chi phí giống**  
* **Chi phí phân bón**  
* **Chi phí thuốc/sinh học**  
* **Chi phí nước**  
* **Chi phí nhân công**  
* **Chi phí thuê đất nếu có**  
* **Chi phí máy móc nếu có**  
* **Chi phí phát sinh**  
* **Chi phí khác**

**Chỉ số cần tính:**

* **Tổng chi phí/lứa**  
* **Chi phí/kg thu hoạch thô**  
* **Chi phí/kg loại A**  
* **Chi phí/kg giao khách**  
* **Lợi nhuận dự kiến nếu có giá bán**  
* **Lợi nhuận thực tế nếu có doanh thu**

---

# **26\. Báo cáo**

## **26.1. Báo cáo sản xuất**

* **Sản lượng theo ngày/tháng/năm**  
* **Sản lượng theo farm**  
* **Sản lượng theo lô đất**  
* **Sản lượng theo cây trồng**  
* **Sản lượng theo lứa trồng**  
* **Sản lượng loại A/B/C/loại bỏ**  
* **So sánh kế hoạch và thực tế**

## **26.2. Báo cáo năng suất**

* **Năng suất kg/m2**  
* **Năng suất kg/cây**  
* **Năng suất theo đầu vụ/chính vụ/cuối vụ**  
* **Năng suất theo farm**  
* **Năng suất theo lô đất**  
* **Năng suất theo mùa vụ**  
* **Min/avg/max sau nhiều vụ**

## **26.3. Báo cáo hao hụt**

* **Hao hụt thu hoạch**  
* **Hao hụt sơ chế**  
* **Hao hụt đóng gói/giao hàng**  
* **Tỷ lệ loại bỏ**  
* **Lý do hàng không đạt**  
* **Lý do hàng bị trả**

## **26.4. Báo cáo chi phí**

* **Chi phí theo lứa trồng**  
* **Chi phí theo farm**  
* **Chi phí theo cây trồng**  
* **Chi phí/kg**  
* **Chi phí nhân công**  
* **Chi phí nước/phân/thuốc**  
* **So sánh chi phí dự kiến và thực tế**

## **26.5. Báo cáo chất lượng và trả hàng**

* **Tỷ lệ hàng trả**  
* **Lý do trả hàng**  
* **Farm/lô có tỷ lệ trả cao**  
* **Cây trồng có tỷ lệ lỗi cao**  
* **Khách hàng có tỷ lệ trả cao**

## **26.6. Báo cáo cải tiến sau vụ**

**Sau mỗi lứa trồng cần có đánh giá:**

* **Tổng sản lượng dự kiến**  
* **Tổng sản lượng thực tế**  
* **Tỷ lệ đạt loại A/B/C**  
* **Tỷ lệ loại bỏ**  
* **Hao hụt thực tế**  
* **Số ngày sinh trưởng thực tế**  
* **Số ngày thu hoạch thực tế**  
* **Tổng nước sử dụng**  
* **Tổng phân/thuốc sử dụng**  
* **Tổng công nhân công**  
* **Chi phí vật tư**  
* **Chi phí nhân công**  
* **Vấn đề phát sinh**  
* **Nguyên nhân chính**  
* **Đề xuất cải tiến vụ sau**

---

# **27\. Màn hình nhập số lượng để xem kế hoạch**

**Đây là màn hình quan trọng nhất của hệ thống.**

## **27.1. Input**

**Người dùng nhập:**

* **Loại nông sản**  
* **Số lượng cần giao**  
* **Đơn vị:**  
  * **kg**  
  * **trái**  
  * **bó**  
  * **thùng**  
* **Tần suất:**  
  * **một lần**  
  * **mỗi ngày**  
  * **mỗi tuần**  
  * **mỗi tháng**  
  * **theo mùa**  
* **Ngày bắt đầu giao**  
* **Ngày kết thúc nếu là cung ứng định kỳ**  
* **Farm ưu tiên nếu có**  
* **Chuẩn thành phẩm**  
* **Hệ số an toàn nếu muốn trồng dư**

## **27.2. Output**

**Hệ thống hiển thị:**

* **Sản lượng thành phẩm cần giao**  
* **Sản lượng cần thu thô**  
* **Số cây cần đang ở giai đoạn thu**  
* **Số cây cần trồng**  
* **M2 đất cần dùng/ngày**  
* **M2 đất cần dùng cho cả chu kỳ**  
* **Số lứa trồng cần có**  
* **Ngày cần bắt đầu làm đất**  
* **Ngày cần gieo/trồng**  
* **Ngày bắt đầu thu hoạch**  
* **Lịch thu dự kiến**  
* **Lịch tưới**  
* **Lịch phân bón**  
* **Công việc chăm sóc**  
* **Nhu cầu nhân công:**  
  * **số công**  
  * **số giờ**  
* **Nhu cầu nước**  
* **Nhu cầu phân**  
* **Dự kiến chi phí**  
* **Dự kiến sản lượng min/avg/max**  
* **Cảnh báo thiếu đất/thiếu nhân công/thiếu sản lượng**  
* **Gợi ý farm/lô khả dụng nếu có dữ liệu**

---

# **28\. Dữ liệu lõi đề xuất**

**Các entity chính:**

* **User**  
* **Role**  
* **Farm**  
* **Plot**  
* **Bed**  
* **Crop**  
* **Crop Variety**  
* **Crop Production Profile**  
* **Product Standard**  
* **Growth Stage**  
* **Stage Task Template**  
* **Irrigation Norm**  
* **Fertilizer Norm**  
* **Chemical/Biological Product**  
* **Harvest Model**  
* **Harvest Phase**  
* **Loss Profile**  
* **Labor Norm**  
* **Supply Contract**  
* **Demand Plan**  
* **Production Plan**  
* **Planting Batch**  
* **Planting Batch Plot Allocation**  
* **Work Task**  
* **Farming Log**  
* **Incident Log**  
* **Chemical Usage Log**  
* **Pre-harvest Inspection**  
* **Harvest Lot**  
* **Harvest Grade Breakdown**  
* **Processing/Sơ chế Record**  
* **Packing Lot**  
* **Packing Lot Source**  
* **Traceability QR**  
* **Delivery Note**  
* **Delivery Acceptance Record**  
* **Return Record**  
* **Post-season Review**  
* **Cost Record**  
* **Alert**  
* **Approval**

---

# **29\. Rủi ro thiết kế cần tránh**

1. **Không hard-code định mức cho cây trồng.**  
2. **Không thiết kế một lứa trồng chỉ thuộc một lô đất.**  
3. **Không thiết kế một lô đóng gói chỉ lấy từ một lô thu hoạch.**  
4. **Không gom kế hoạch và thực tế vào cùng một dữ liệu.**  
5. **Không chỉ hiển thị QR từ kế hoạch; QR phải dựa trên dữ liệu thực tế.**  
6. **Không tự động cập nhật định mức sau vụ nếu chưa có quản lý duyệt.**  
7. **Không bỏ qua lịch sử đất, vì sẽ ảnh hưởng năng suất và sâu bệnh.**  
8. **Không bỏ qua thời gian cách ly nếu có dùng thuốc/sinh học.**  
9. **Không chỉ tính kg, phải tính cả cây, m2, lứa, ngày, công, giờ, nước, chi phí.**  
10. **Không làm hệ thống thành CRM đơn thuần; core thật là sản xuất nông nghiệp \+ truy xuất.**

---

# **30\. Kết luận**

**Hệ thống cần được thiết kế xoay quanh chuỗi:**

**Nhu cầu cung ứng**  
**→ Kế hoạch sản xuất**  
**→ Lứa trồng**  
**→ Công việc canh tác**  
**→ Nhật ký thực tế**  
**→ Nghiệm thu trước thu hoạch**  
**→ Thu hoạch**  
**→ Sơ chế**  
**→ Đóng gói**  
**→ QR truy xuất**  
**→ Giao hàng**  
**→ Trả hàng nếu có**  
**→ Báo cáo sau vụ**  
**→ Cải tiến định mức**

**Core tính toán không nên bắt đầu từ kg hay diện tích đơn lẻ, mà nên đi theo mô hình:**

**m2 → số cây → sản lượng → hao hụt → thành phẩm giao khách**

**Đối với cây thu nhiều lần, cần tính theo:**

**Đầu vụ → chính vụ → cuối vụ**

**Đối với quản lý nhiều farm, cần hỗ trợ:**

**Farm → lô đất → luống → lứa trồng → lô thu hoạch → lô đóng gói → QR**

**MVP nên ưu tiên màn hình:**

**Nhập số lượng cần giao → hệ thống trả ra kế hoạch trồng, tài nguyên, nhân công, chi phí và cảnh báo.**


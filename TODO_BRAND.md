# AgriOps - TODO: Hoàn thiện theo BRD v1

**Ngày tạo:** 2026-05-13
**Trạng thái:** Còn thiếu nhiều module so với BRD
**Ưu tiên:** HIGH → MEDIUM → LOW

---

## 🚨 HIGH PRIORITY - Cần làm trước

### 1. Sơ chế (Processing) - BRD Section 17
**Mục tiêu:** Phiếu sơ chế sau thu hoạch

**Cần thêm:**
- [ ] Bảng `processing_records` đã có, nhưng flow chưa hoàn chỉnh
- [ ] API tạo phiếu sơ chế với yield trước/sau sơ chế
- [ ] Lưu lý do loại bỏ trong sơ chế
- [ ] Xác nhận "đạt chuẩn đóng gói" trên phiếu sơ chế
- [ ] Gắn người xác nhận và thời gian

**Tài liệu tham khảo:** BRD Section 17 - Sơ chế

---

### 2. Báo cáo sau vụ (Post-season Review) - BRD Section 26.6
**Mục tiêu:** Đánh giá sau mỗi lứa trồng để cải tiến

**Cần thêm:**
- [ ] Bảng `post_season_reviews` (chưa có)
- [ ] API tạo/cập nhật review sau vụ
- [ ] Lưu: sản lượng dự kiến vs thực tế, tỷ lệ A/B/C, hao hụt thực tế
- [ ] Lưu: ngày sinh trưởng thực tế, ngày thu hoạch thực tế
- [ ] Lưu: tổng nước/phân/thuốc sử dụng
- [ ] Lưu: tổng công nhân công
- [ ] Lưu: chi phí vật tư thực tế
- [ ] Lưu: vấn đề phát sinh, nguyên nhân chính
- [ ] Lưu: đề xuất cải tiến cho vụ sau

**Tài liệu tham khảo:** BRD Section 26.6 - Báo cáo cải tiến sau vụ

---

### 3. Lịch sử đất (Soil History) - BRD Section 5.4
**Mục tiêu:** Theo dõi lịch sử đất để phục vụ cải tiến và cảnh báo luân canh

**Cần thêm:**
- [ ] Bảng `soil_history` (chưa có)
- [ ] Lưu: vụ trước trồng cây gì, lứa nào
- [ ] Lưu: thời gian trồng, sản lượng thực tế
- [ ] Lưu: tỷ lệ hao hụt vụ trước
- [ ] Lưu: sâu bệnh từng gặp
- [ ] Lưu: phân/thuốc đã dùng vụ trước
- [ ] Lưu: thời gian nghỉ đất
- [ ] Lưu: lần cải tạo đất gần nhất
- [ ] Ghi chú bất thường
- [ ] Allocation service check soil history khi phân bổ lứa mới

**Tài liệu tham khảo:** BRD Section 5.4 - Lịch sử đất

---

## ⚠️ MEDIUM PRIORITY

### 4. Danh mục thuốc/sinh học (Chemical Product Catalog) - BRD Section 8.3
**Mục tiêu:** Quản lý danh mục sản phẩm bảo vệ thực vật

**Cần thêm:**
- [ ] Bảng `chemical_products` (chưa có)
- [ ] Lưu: tên thuốc/chế phẩm, loại (thuốc sâu, thuốc trừ bệnh, sinh học)
- [ ] Lưu: hoạt chất
- [ ] Lưu: cây trồng được phép dùng
- [ ] Lưu: liều lượng khuyến nghị
- [ ] Lưu: thời gian cách ly mặc định
- [ ] Lưu: trạng thái (được phép/cấm)
- [ ] API CRUD cho danh mục
- [ ] ChemicalUsage gắn product từ catalog

**Tài liệu tham khảo:** BRD Section 8.3 - Thuốc/sinh học

---

### 5. Alerts tự động - BRD Section 24
**Mục tiêu:** Hệ thống cảnh báo tự động theo sự kiện

**Cần thêm:**
- [ ] AlertService trigger tự động cho các sự kiện:
  - [ ] Tới lịch tưới
  - [ ] Trễ việc (task overdue)
  - [ ] Chưa hoàn thành công việc bắt buộc
  - [ ] Chưa đủ thời gian cách ly
  - [ ] Chuẩn bị đến ngày thu hoạch
  - [ ] Chưa nghiệm thu trước thu hoạch
  - [ ] Thiếu sản lượng so với kế hoạch
  - [ ] Lô đất chưa sẵn sàng
  - [ ] Tỷ lệ hao hụt cao bất thường
  - [ ] Chi phí vượt dự kiến
- [ ] Alert preferences per user/role
- [ ] Notification channel (email/push/in-app)

**Tài liệu tham khảo:** BRD Section 24 - Cảnh báo tự động

---

### 6. Điều phối nhiều farm - BRD Section 22
**Mục tiêu:** Hỗ trợ điều phối khi farm chính thiếu hàng

**Cần thêm:**
- [ ] API xem sản lượng khả dụng ở farm khác
- [ ] API chọn farm bổ sung thủ công
- [ ] Ghi nhận số lượng lấy từ farm nào (trong PackingLotSource)
- [ ] Cảnh báo thiếu hàng khi demand cao hơn supply
- [ ] QR phải hiển thị nhiều nguồn farm khi trộn

**Tài liệu tham khảo:** BRD Section 22 - Điều phối nhiều farm

---

## 📝 LOW PRIORITY

### 7. Chi phí chi tiết - BRD Section 25
**Mục tiêu:** Tính chi phí đầy đủ theo từng loại

**Cần thêm:**
- [ ] Bảng `cost_records` đã có, cần mở rộng loại chi phí:
  - [ ] Chi phí giống (seed cost)
  - [ ] Chi phí nước (water cost)
  - [ ] Chi phí máy móc (equipment cost)
  - [ ] Chi phí thuê đất (land rental)
  - [ ] Chi phí phát sinh khác
- [ ] Tổng hợp chi phí theo lứa trồng
- [ ] Tính chi phí/kg theo từng grade

**Tài liệu tham khảo:** BRD Section 25 - Chi phí sản xuất

---

### 8. Lịch tưới thực tế - BRD Section 14.2
**Mục tiêu:** Ghi nhận lịch tưới thực tế

**Cần thêm:**
- [ ] Bảng `irrigation_logs` (chưa có)
- [ ] Lưu: ngày tưới, lượng nước thực tế
- [ ] Gắn với batch/plot
- [ ] Lưu người thực hiện, ghi chú

---

### 9. Bón phân thực tế - BRD Section 14.2
**Mục tiêu:** Ghi nhận bón phân thực tế

**Cần thêm:**
- [ ] Bảng `fertilizer_logs` (chưa có)
- [ ] Lưu: ngày bón, loại phân, số lượng
- [ ] Gắn với batch/plot
- [ ] Lưu chi phí nếu có

---

### 10. Multi-harvest phases - BRD Section 9.2
**Mục tiêu:** Tính yield theo đầu vụ/chính vụ/cuối vụ

**Cần thêm:**
- [ ] HarvestModel đã có `harvest_phase`, cần logic tính:
  - [ ] Hệ số năng suất theo phase
  - [ ] Ngày bắt đầu/kết thúc mỗi phase
  - [ ] Tính tổng yield = sum(phase_yield)

---

### 11. Báo cáo (Reports) - BRD Section 26
**Mục tiêu:** Các báo cáo tổng hợp

**Cần thêm:**
- [ ] Report endpoints cho:
  - [ ] Báo cáo sản lượng theo ngày/tháng/năm
  - [ ] Báo cáo sản lượng theo farm/lô/cây/lứa
  - [ ] Báo cáo năng suất (kg/m2, kg/cây)
  - [ ] Báo cáo hao hụt theo loại
  - [ ] Báo cáo chi phí theo lứa/farm/cây
  - [ ] Báo cáo chất lượng và trả hàng

---

### 12. Work Task Checklist - BRD Section 13.3
**Mục tiêu:** Checklist chi tiết cho công việc

**Cần thêm:**
- [ ] Stage task template đã có, cần mở rộng:
  - [ ] Checklist items có thể tích hợp vào WorkTask
  - [ ] Checklist completion tracking
  - [ ] Checklist photo requirement

---

## ✅ ĐÃ CÓ (Không cần làm)

- Farm/Plot/Bed CRUD
- Crop/Variety/Norms
- Auth + RBAC 7 roles
- Planning Engine (T4)
- Planting Batch Lifecycle
- Work Tasks + Farming Logs
- Incidents + Chemical Usage + Isolation
- Pre-harvest Inspection + Approval
- Harvest Lots + Grade A/B/C
- Packing Lots (multi-source)
- QR Traceability (public)
- Delivery + Return
- Alerts (basic model có sẵn)

---

## Ghi chú kỹ thuật

### 3 quan hệ critical đã đúng:
1. ✅ 1 lứa trồng có thể nhiều lô/luống (PlantingBatchAllocation - many-to-many)
2. ✅ 1 lô đóng gói có thể nhiều nguồn thu hoạch (PackingLotSource - many-to-many)
3. ✅ Kế hoạch và thực tế tách dữ liệu (ProductionPlan vs FarmingLog/HarvestLot)

### Những thứ không nên tự động:
- Không tự động cập nhật định mức sau vụ
- Không để AI tự duyệt/cập nhật
- Không tự động chọn farm thay thế (chỉ cảnh báo)

---

## Thứ tự ưu tiên đề xuất:

1. **Post-season Review** - Quan trọng cho MVP hoàn chỉnh
2. **Processing (Sơ chế)** - Flow thu hoạch chưa complete
3. **Soil History** - Ảnh hưởng allocation guard
4. **Chemical Product Catalog** - Quản lý thuốc chưa chuẩn
5. **Alerts tự động** - Cảnh báo người dùng
6. **Multi-farm coordination** - Cần cho business thực
7. **Chi phí chi tiết** - Báo cáo tài chính
8. **Irrigation/Fertilizer logs** - Nhật ký canh tác đầy đủ
9. **Reports** - Dashboard cho quản lý
10. **Checklist enhancement** - UX improvement
import { test } from "../../setup";
import { ThemeGalleryPage } from "../../pages/admin/appearance/ThemeGalleryPage";

test.describe("theme gallery", () => {
    test("should list only the installed themes", async ({
        adminPage,
    }) => {
        const themeGalleryPage = new ThemeGalleryPage(adminPage);

        await themeGalleryPage.expectOnlyInstalledThemes();
    });

    test("should upload, preview and activate an HTML template", async ({
        adminPage,
    }) => {
        const themeGalleryPage = new ThemeGalleryPage(adminPage);

        await themeGalleryPage.uploadTheme("e2e-template.zip", "E2E Template");

        await themeGalleryPage.expectPreviewShows(
            "E2E Template",
            "E2E Template Welcome",
        );

        await themeGalleryPage.activateTheme("E2E Template");

        await themeGalleryPage.expectStorefrontShows("E2E Template Welcome");

        await themeGalleryPage.activateTheme("Default");

        await themeGalleryPage.deleteTheme("E2E Template");
    });
});
